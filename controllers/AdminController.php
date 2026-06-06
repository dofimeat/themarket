<?php

namespace app\controllers;

use app\models\Brand;
use app\models\Order;
use app\models\Product;
use app\models\ProductReview;
use app\models\User;
use Yii;
use yii\data\Pagination;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Админ-панель: управление пользователями, брендами, товарами.
 */
class AdminController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function () {
                            $user = Yii::$app->user->identity;
                            return $user !== null && $user->isAdmin();
                        },
                    ],
                ],
                'denyCallback' => function () {
                    Yii::$app->session->setFlash('warning', 'Доступ запрещён.');
                    return Yii::$app->response->redirect(['/site/index']);
                },
            ],
        ];
    }

    public function actionIndex()
    {
        $userCount = (int) User::find()->count();
        $brandCount = (int) Brand::find()->count();
        $productCount = (int) Product::find()->count();
        $pendingBrands = (int) Brand::find()->where(['status' => Brand::STATUS_PENDING])->count();
        $pendingProducts = (int) Product::find()->where(['status' => Product::STATUS_PENDING])->count();
        $orderCount = (int) Order::find()->count();
        $pendingOrders = (int) Order::find()->where(['status' => Order::STATUS_NEW])->count();
        $reviewCount = (int) ProductReview::find()->count();
        $pendingReviews = (int) ProductReview::find()->where(['status' => ProductReview::STATUS_PENDING])->count();

        return $this->render('index', [
            'userCount' => $userCount,
            'brandCount' => $brandCount,
            'productCount' => $productCount,
            'pendingBrands' => $pendingBrands,
            'pendingProducts' => $pendingProducts,
            'orderCount' => $orderCount,
            'pendingOrders' => $pendingOrders,
            'reviewCount' => $reviewCount,
            'pendingReviews' => $pendingReviews,
        ]);
    }

    // ===========================
    // Users
    // ===========================

    public function actionUsers()
    {
        $query = User::find()->orderBy(['id' => SORT_DESC]);

        $search = trim((string) Yii::$app->request->get('search', ''));
        if ($search !== '') {
            $query->andWhere([
                'or',
                ['like', 'email', $search],
                ['like', 'username', $search],
                ['like', 'first_name', $search],
                ['like', 'last_name', $search],
            ]);
        }

        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => 20,
        ]);
        $users = $query->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('users', [
            'users' => $users,
            'pages' => $pages,
            'search' => $search,
        ]);
    }

    public function actionUserUpdate(int $id)
    {
        $user = User::findOne($id);
        if ($user === null) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }

        if (Yii::$app->request->isPost) {
            $role = (string) Yii::$app->request->post('role', $user->role);
            $status = (string) Yii::$app->request->post('status', $user->status ?? User::STATUS_ACTIVE);
            $user->role = $role;
            if ($user->hasAttribute('status')) {
                $user->status = $status;
            }
            if ($user->save(false)) {
                Yii::$app->session->setFlash('success', 'Пользователь обновлён.');
                return $this->redirect(['users']);
            }
        }

        return $this->render('user-update', [
            'user' => $user,
        ]);
    }

    public function actionUserDelete(int $id): Response
    {
        $user = User::findOne($id);
        if ($user === null) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }
        if ((int) $user->id === (int) Yii::$app->user->id) {
            Yii::$app->session->setFlash('danger', 'Нельзя удалить самого себя.');
            return $this->redirect(['users']);
        }
        $user->delete();
        Yii::$app->session->setFlash('success', 'Пользователь удалён.');
        return $this->redirect(['users']);
    }

    // ===========================
    // Brands
    // ===========================

    public function actionBrands()
    {
        $query = Brand::find()->orderBy(['id' => SORT_DESC]);

        $statusFilter = (string) Yii::$app->request->get('status', '');
        if ($statusFilter !== '' && in_array($statusFilter, [Brand::STATUS_PENDING, Brand::STATUS_APPROVED, Brand::STATUS_REJECTED], true)) {
            $query->andWhere(['status' => $statusFilter]);
        }

        $search = trim((string) Yii::$app->request->get('search', ''));
        if ($search !== '') {
            $query->andWhere(['like', 'name', $search]);
        }

        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => 20,
        ]);
        $brands = $query->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('brands', [
            'brands' => $brands,
            'pages' => $pages,
            'statusFilter' => $statusFilter,
            'search' => $search,
        ]);
    }

    public function actionBrandUpdate(int $id)
    {
        $brand = Brand::findOne($id);
        if ($brand === null) {
            throw new NotFoundHttpException('Бренд не найден.');
        }

        if (Yii::$app->request->isPost) {
            $brand->name = (string) Yii::$app->request->post('name', $brand->name);
            $brand->city = (string) Yii::$app->request->post('city', $brand->city);
            $brand->description = (string) Yii::$app->request->post('description', $brand->description);
            if ($brand->save(false)) {
                Yii::$app->session->setFlash('success', 'Бренд обновлён.');
                return $this->redirect(['brands']);
            }
        }

        return $this->render('brand-update', [
            'brand' => $brand,
        ]);
    }

    public function actionBrandDelete(int $id): Response
    {
        $brand = Brand::findOne($id);
        if ($brand === null) {
            throw new NotFoundHttpException('Бренд не найден.');
        }
        $brand->delete();
        Yii::$app->session->setFlash('success', 'Бренд удалён.');
        return $this->redirect(['brands']);
    }

    public function actionBrandBlock(int $id): Response
    {
        $brand = Brand::findOne($id);
        if ($brand === null) {
            throw new NotFoundHttpException('Бренд не найден.');
        }
        $brand->is_blocked = $brand->isBlocked() ? 0 : 1;
        $brand->save(false);
        $msg = $brand->isBlocked() ? 'Бренд заблокирован.' : 'Бренд разблокирован.';
        Yii::$app->session->setFlash('success', $msg);
        return $this->redirect(['brands']);
    }

    public function actionBrandApprove(int $id): Response
    {
        $brand = Brand::findOne($id);
        if ($brand === null) {
            throw new NotFoundHttpException('Бренд не найден.');
        }
        $brand->status = Brand::STATUS_APPROVED;
        $brand->is_blocked = 0;
        $brand->save(false);

        $ownerId = (int) $brand->user_id;
        if ($ownerId > 0) {
            $owner = User::findOne($ownerId);
            if ($owner !== null && $owner->hasAttribute('role')) {
                $owner->setAttribute('role', User::ROLE_SELLER);
                $owner->save(false);
            }
        }

        Yii::$app->session->setFlash('success', 'Бренд одобрен. Пользователю назначена роль продавца.');
        return $this->redirect(['brands']);
    }

    public function actionBrandReject(int $id): Response
    {
        $brand = Brand::findOne($id);
        if ($brand === null) {
            throw new NotFoundHttpException('Бренд не найден.');
        }
        $brand->status = Brand::STATUS_REJECTED;
        $brand->save(false);
        Yii::$app->session->setFlash('success', 'Бренд отклонён.');
        return $this->redirect(['brands']);
    }

    // ===========================
    // Products
    // ===========================

    public function actionProducts()
    {
        $query = (new Query())
            ->select([
                'p.id',
                'p.name',
                'p.price',
                'p.status',
                'p.created_at',
                'brand_name' => 'b.name',
                'image' => 'COALESCE(main_img.image, any_img.image)',
            ])
            ->from(['p' => 'products'])
            ->leftJoin(['b' => 'brands'], 'b.id = p.brand_id')
            ->leftJoin(
                ['main_img' => 'product_images'],
                'main_img.product_id = p.id AND main_img.is_main = 1'
            )
            ->leftJoin(
                ['any_img' => 'product_images'],
                'any_img.id = (
                    SELECT MIN(pi.id)
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                )'
            )
            ->orderBy(['p.id' => SORT_DESC]);

        $statusFilter = (string) Yii::$app->request->get('status', '');
        if ($statusFilter !== '' && in_array($statusFilter, [Product::STATUS_DRAFT, Product::STATUS_PENDING, Product::STATUS_PUBLISHED, Product::STATUS_REJECTED], true)) {
            $query->andWhere(['p.status' => $statusFilter]);
        }

        $search = trim((string) Yii::$app->request->get('search', ''));
        if ($search !== '') {
            $query->andWhere(['like', 'p.name', $search]);
        }

        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => (int) $countQuery->count(),
            'pageSize' => 20,
        ]);
        $products = $query->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('products', [
            'products' => $products,
            'pages' => $pages,
            'statusFilter' => $statusFilter,
            'search' => $search,
        ]);
    }

    public function actionProductUpdate(int $id)
    {
        $product = Product::findOne($id);
        if ($product === null) {
            throw new NotFoundHttpException('Товар не найден.');
        }

        if (Yii::$app->request->isPost) {
            $product->name = (string) Yii::$app->request->post('name', $product->name);
            $product->description = (string) Yii::$app->request->post('description', $product->description);
            $priceRaw = Yii::$app->request->post('price', $product->price);
            $priceVal = is_numeric(str_replace([' ', ','], ['', '.'], (string) $priceRaw)) ? (float) str_replace([' ', ','], ['', '.'], (string) $priceRaw) : $product->price;
            $product->price = $priceVal;
            if ($product->save(false)) {
                Yii::$app->session->setFlash('success', 'Товар обновлён.');
                return $this->redirect(['products']);
            }
        }

        return $this->render('product-update', [
            'product' => $product,
        ]);
    }

    public function actionProductDelete(int $id): Response
    {
        $product = Product::findOne($id);
        if ($product === null) {
            throw new NotFoundHttpException('Товар не найден.');
        }
        $product->delete();
        Yii::$app->session->setFlash('success', 'Товар удалён.');
        return $this->redirect(['products']);
    }

    public function actionProductStatus(int $id, string $status): Response
    {
        $product = Product::findOne($id);
        if ($product === null) {
            throw new NotFoundHttpException('Товар не найден.');
        }
        $allowed = [Product::STATUS_DRAFT, Product::STATUS_PENDING, Product::STATUS_PUBLISHED, Product::STATUS_REJECTED];
        if (!in_array($status, $allowed, true)) {
            Yii::$app->session->setFlash('danger', 'Недопустимый статус.');
            return $this->redirect(['products']);
        }
        $product->status = $status;
        $product->save(false);
        $labels = [
            Product::STATUS_DRAFT => 'Черновик',
            Product::STATUS_PENDING => 'На модерации',
            Product::STATUS_PUBLISHED => 'Опубликован',
            Product::STATUS_REJECTED => 'Отклонён',
        ];
        Yii::$app->session->setFlash('success', 'Статус изменён на «' . ($labels[$status] ?? $status) . '».');
        return $this->redirect(['products']);
    }

    // ===========================
    // Orders
    // ===========================

    public function actionOrders()
    {
        $query = Order::find()->with('items')->orderBy(['id' => SORT_DESC]);

        $statusFilter = (string) Yii::$app->request->get('status', '');
        $allowedStatuses = [
            Order::STATUS_NEW,
            Order::STATUS_PAID,
            Order::STATUS_SHIPPED,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
        ];
        if ($statusFilter !== '' && in_array($statusFilter, $allowedStatuses, true)) {
            $query->andWhere(['status' => $statusFilter]);
        }

        $search = trim((string) Yii::$app->request->get('search', ''));
        if ($search !== '') {
            $query->andWhere([
                'or',
                ['like', 'email', $search],
                ['like', 'first_name', $search],
                ['like', 'last_name', $search],
                ['like', 'phone', $search],
            ]);
        }

        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => (int) $countQuery->count(),
            'pageSize' => 20,
        ]);
        $orders = $query->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('orders', [
            'orders' => $orders,
            'pages' => $pages,
            'statusFilter' => $statusFilter,
            'search' => $search,
        ]);
    }

    public function actionOrderView(int $id)
    {
        $order = Order::find()->with('items')->where(['id' => $id])->one();
        if ($order === null) {
            throw new NotFoundHttpException('Заказ не найден.');
        }

        return $this->render('order-view', [
            'order' => $order,
        ]);
    }

    public function actionOrderStatus(int $id, string $status): Response
    {
        $order = Order::findOne($id);
        if ($order === null) {
            throw new NotFoundHttpException('Заказ не найден.');
        }
        $allowed = [
            Order::STATUS_NEW,
            Order::STATUS_PAID,
            Order::STATUS_SHIPPED,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
        ];
        if (!in_array($status, $allowed, true)) {
            Yii::$app->session->setFlash('danger', 'Недопустимый статус.');
            return $this->redirect(['orders']);
        }
        $order->status = $status;
        $order->save(false);
        Yii::$app->session->setFlash('success', 'Статус заказа изменён на «' . $order->getStatusLabel() . '».');
        return $this->redirect(['order-view', 'id' => $order->id]);
    }

    // ===========================
    // Reviews
    // ===========================

    public function actionReviews()
    {
        $query = (new Query())
            ->select([
                'r.id',
                'r.product_id',
                'r.user_id',
                'r.rating',
                'r.text',
                'r.status',
                'r.created_at',
                'product_name' => 'p.name',
                'user_email' => 'u.email',
                'user_first_name' => 'u.first_name',
                'user_last_name' => 'u.last_name',
            ])
            ->from(['r' => 'product_reviews'])
            ->leftJoin(['p' => 'products'], 'p.id = r.product_id')
            ->leftJoin(['u' => 'users'], 'u.id = r.user_id')
            ->orderBy(['r.id' => SORT_DESC]);

        $statusFilter = (string) Yii::$app->request->get('status', '');
        if ($statusFilter !== '' && in_array($statusFilter, [ProductReview::STATUS_PENDING, ProductReview::STATUS_APPROVED, ProductReview::STATUS_REJECTED], true)) {
            $query->andWhere(['r.status' => $statusFilter]);
        }

        $search = trim((string) Yii::$app->request->get('search', ''));
        if ($search !== '') {
            $query->andWhere([
                'or',
                ['like', 'r.text', $search],
                ['like', 'p.name', $search],
                ['like', 'u.email', $search],
            ]);
        }

        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => (int) $countQuery->count(),
            'pageSize' => 20,
        ]);
        $reviews = $query->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('reviews', [
            'reviews' => $reviews,
            'pages' => $pages,
            'statusFilter' => $statusFilter,
            'search' => $search,
        ]);
    }

    public function actionReviewApprove(int $id): Response
    {
        $review = ProductReview::findOne($id);
        if ($review === null) {
            throw new NotFoundHttpException('Отзыв не найден.');
        }
        $review->status = ProductReview::STATUS_APPROVED;
        $review->save(false);
        Yii::$app->session->setFlash('success', 'Отзыв одобрен.');
        return $this->redirect(['reviews']);
    }

    public function actionReviewReject(int $id): Response
    {
        $review = ProductReview::findOne($id);
        if ($review === null) {
            throw new NotFoundHttpException('Отзыв не найден.');
        }
        $review->status = ProductReview::STATUS_REJECTED;
        $review->save(false);
        Yii::$app->session->setFlash('success', 'Отзыв отклонён.');
        return $this->redirect(['reviews']);
    }

    public function actionReviewDelete(int $id): Response
    {
        $review = ProductReview::findOne($id);
        if ($review === null) {
            throw new NotFoundHttpException('Отзыв не найден.');
        }
        $review->delete();
        Yii::$app->session->setFlash('success', 'Отзыв удалён.');
        return $this->redirect(['reviews']);
    }
}
