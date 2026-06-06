<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Query;
use app\models\ProductReview;

class ReviewController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['add'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Add review via AJAX POST.
     * Only users who have purchased this product can leave a review.
     */
    public function actionAdd(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $productId = (int) Yii::$app->request->post('product_id', 0);
        $rating = (int) Yii::$app->request->post('rating', 5);
        $text = trim((string) Yii::$app->request->post('text', ''));

        if ($productId <= 0) {
            return ['success' => false, 'error' => 'Товар не указан.'];
        }

        if ($text === '') {
            return ['success' => false, 'error' => 'Напишите текст отзыва.'];
        }

        if ($rating < ProductReview::MIN_RATING || $rating > ProductReview::MAX_RATING) {
            $rating = ProductReview::MAX_RATING;
        }

        $uid = (int) Yii::$app->user->id;

        // Check that user has purchased this product (order exists with this product)
        $purchased = (new Query())
            ->from(['oi' => 'order_items'])
            ->innerJoin(['o' => 'orders'], 'o.id = oi.order_id')
            ->where(['o.user_id' => $uid, 'oi.product_id' => $productId])
            ->exists();

        if (!$purchased) {
            return ['success' => false, 'error' => 'Отзывы могут оставлять только покупатели.'];
        }

        // Check if user already reviewed this product
        $existing = ProductReview::find()
            ->where(['user_id' => $uid, 'product_id' => $productId])
            ->one();

        if ($existing !== null) {
            if ($existing->status === ProductReview::STATUS_PENDING) {
                return ['success' => false, 'error' => 'Ваш отзыв уже отправлен на модерацию. Ожидайте публикации.'];
            }
            if ($existing->status === ProductReview::STATUS_REJECTED) {
                return ['success' => false, 'error' => 'Ваш предыдущий отзыв был отклонён. Вы можете обратиться к администрации.'];
            }
            return ['success' => false, 'error' => 'Вы уже оставили отзыв на этот товар.'];
        }

        $review = new ProductReview();
        $review->product_id = $productId;
        $review->user_id = $uid;
        $review->rating = $rating;
        $review->text = $text;
        $review->status = ProductReview::STATUS_PENDING;

        try {
            if (!$review->save()) {
                $errors = $review->getFirstErrors();
                return ['success' => false, 'error' => reset($errors) ?: 'Ошибка сохранения.'];
            }
        } catch (\Throwable $e) {
            Yii::error('Review save error: ' . $e->getMessage(), 'review');
            // Check if it's a duplicate key error
            if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'uk_review') !== false) {
                return ['success' => false, 'error' => 'Вы уже оставили отзыв на этот товар.'];
            }
            return ['success' => false, 'error' => 'Произошла ошибка при сохранении отзыва. Попробуйте позже.'];
        }

        // Return the new review data for JS rendering
        $user = Yii::$app->user->identity;
        $displayName = $user->getDisplayName();
        $avatar = $user->getAvatarPath();

        $createdAt = $review->created_at;
        if ($createdAt === null || $createdAt === '') {
            $createdAt = date('Y-m-d H:i:s');
        }

        return [
            'success' => true,
            'message' => 'Отзыв отправлен на модерацию.',
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'text' => $review->text,
                'created_at' => date('d.m.Y H:i', strtotime($createdAt)),
                'user_name' => $displayName,
                'user_avatar' => $avatar,
            ],
        ];
    }
}
