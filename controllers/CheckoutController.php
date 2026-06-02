<?php

namespace app\controllers;

use app\models\CheckoutForm;
use app\models\Order;
use app\models\OrderItem;
use Yii;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Оформление заказа: форма, подтверждение, успех.
 */
class CheckoutController extends Controller
{
    public function init()
    {
        parent::init();
        $this->setViewPath('@app/views/site');
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
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
     * Страница оформления заказа.
     */
    public function actionIndex()
    {
        $cart = CartController::getCart();
        if (empty($cart)) {
            return $this->redirect(['/cart']);
        }

        $model = new CheckoutForm();

        // Pre-fill from user profile
        $user = Yii::$app->user->identity;
        if ($user !== null) {
            $model->email = (string) $user->email;
            $model->first_name = (string) ($user->first_name ?? '');
            $model->last_name = (string) ($user->last_name ?? '');
        }

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $model->validate()) {
            $orderId = $this->createOrder($model, $cart, (int) $user->id);
            if ($orderId !== null) {
                // Clear cart
                CartController::setCart([]);

                // Redirect to YooKassa or success
                // TODO: YooKassa integration — for now go to success page
                return $this->redirect(['/checkout/success', 'id' => $orderId]);
            }
            Yii::$app->session->setFlash('error', 'Не удалось оформить заказ. Попробуйте снова.');
        }

        $cartTotal = CartController::cartTotal();

        return $this->render('checkout', [
            'model' => $model,
            'cartItems' => $cart,
            'cartTotal' => $cartTotal,
            'deliveryCost' => 0, // TODO: calculate based on delivery method
            'grandTotal' => $cartTotal,
        ]);
    }

    /**
     * Страница успешного заказа.
     */
    public function actionSuccess($id)
    {
        $user = Yii::$app->user->identity;
        $order = Order::findOne(['id' => (int) $id, 'user_id' => (int) $user->id]);
        if ($order === null) {
            throw new NotFoundHttpException('Заказ не найден.');
        }

        return $this->render('order-success', [
            'order' => $order,
        ]);
    }

    /**
     * Create order in DB from cart + form data.
     */
    private function createOrder(CheckoutForm $form, array $cart, int $userId): ?int
    {
        $total = CartController::cartTotal();
        if ($total <= 0) {
            return null;
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $order = new Order();
            $order->user_id = $userId;
            $order->status = Order::STATUS_NEW;
            $order->total_price = $total;
            $order->email = $form->email;
            $order->first_name = $form->first_name;
            $order->last_name = $form->last_name;
            $order->phone = $form->phone;
            $order->country = $form->country;
            $order->city = $form->city;
            $order->address = $form->address;
            $order->postal_code = $form->postal_code;
            $order->delivery_method = $form->delivery_method;
            $order->delivery_cost = 0;
            $order->payment_method = $form->payment_method;
            $order->comment = $form->comment ?: '';
            if ($order->hasAttribute('created_at')) {
                $order->created_at = new Expression('NOW()');
            }

            if (!$order->save(false)) {
                throw new \RuntimeException('Order save failed');
            }

            $orderId = (int) $order->id;

            foreach ($cart as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $orderId;
                $orderItem->product_id = (int) $item['product_id'];
                $orderItem->product_name = (string) ($item['name'] ?? '');
                $orderItem->product_image = (string) ($item['image'] ?? '');
                $orderItem->size = (string) ($item['size'] ?? '');
                $orderItem->price = (float) ($item['price'] ?? 0);
                $orderItem->quantity = (int) ($item['quantity'] ?? 1);
                $orderItem->total = $orderItem->price * $orderItem->quantity;
                if (!$orderItem->save(false)) {
                    throw new \RuntimeException('OrderItem save failed');
                }
            }

            $transaction->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('Order creation failed: ' . $e->getMessage(), 'checkout');
            return null;
        }
    }
}
