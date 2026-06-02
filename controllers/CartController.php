<?php

namespace app\controllers;

use app\models\Product;
use app\models\ProductImage;
use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\web\JsonResponseFormatter;
use yii\web\Response;

/**
 * Корзина: добавление, удаление, изменение количества.
 * Данные хранятся в сессии.
 */
class CartController extends Controller
{
    public function init()
    {
        parent::init();
        $this->setViewPath('@app/views/site');
    }

    /**
     * Добавить товар в корзину (AJAX POST).
     */
    public function actionAdd(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $productId = (int) Yii::$app->request->post('product_id', 0);
        $size = trim((string) Yii::$app->request->post('size', ''));
        $qty = max(1, (int) Yii::$app->request->post('quantity', 1));

        if ($productId <= 0) {
            return $this->asJson(['success' => false, 'error' => 'Товар не указан.']);
        }

        $product = Product::findOne(['id' => $productId, 'status' => Product::STATUS_PUBLISHED]);
        if ($product === null) {
            return $this->asJson(['success' => false, 'error' => 'Товар не найден.']);
        }

        // Validate size exists for this product
        if ($size !== '') {
            $sizeExists = (new Query())
                ->from('product_sizes')
                ->where(['product_id' => $productId, 'size' => $size])
                ->exists();
            if (!$sizeExists) {
                return $this->asJson(['success' => false, 'error' => 'Размер не найден.']);
            }
        }

        $cart = $this->getCart();
        $cartKey = $productId . '_' . $size;

        if (isset($cart[$cartKey])) {
            // One item per size — don't allow duplicates
            return $this->asJson(['success' => false, 'error' => 'Этот товар уже в корзине.']);
        }

        // Fetch product image
        $image = (new Query())
            ->select(['image'])
            ->from('product_images')
            ->where(['product_id' => $productId])
            ->orderBy(['is_main' => SORT_DESC, 'id' => SORT_ASC])
            ->scalar();

        $cart[$cartKey] = [
            'product_id' => $productId,
            'name' => (string) $product->name,
            'price' => (float) $product->price,
            'size' => $size,
            'quantity' => $qty,
            'image' => (string) ($image ?: ''),
        ];

        $this->setCart($cart);

        return $this->asJson([
            'success' => true,
            'count' => $this->cartItemCount(),
            'total' => $this->cartTotal(),
        ]);
    }

    /**
     * Удалить позицию из корзины (AJAX POST).
     */
    public function actionRemove(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $key = trim((string) Yii::$app->request->post('key', ''));
        $cart = $this->getCart();

        if ($key !== '' && isset($cart[$key])) {
            unset($cart[$key]);
        }

        $this->setCart($cart);

        return $this->asJson([
            'success' => true,
            'count' => $this->cartItemCount(),
            'total' => $this->cartTotal(),
        ]);
    }

    /**
     * Обновить количество (AJAX POST).
     */
    public function actionUpdate(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $key = trim((string) Yii::$app->request->post('key', ''));
        $qty = max(1, (int) Yii::$app->request->post('quantity', 1));
        $cart = $this->getCart();

        if ($key !== '' && isset($cart[$key])) {
            $cart[$key]['quantity'] = $qty;
        }

        $this->setCart($cart);

        return $this->asJson([
            'success' => true,
            'count' => $this->cartItemCount(),
            'total' => $this->cartTotal(),
        ]);
    }

    /**
     * Получить количество товаров в корзине (AJAX GET — для бейджа).
     */
    public function actionCount(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->asJson([
            'count' => $this->cartItemCount(),
            'total' => $this->cartTotal(),
        ]);
    }

    /**
     * Страница корзины.
     */
    public function actionIndex()
    {
        $cart = $this->getCart();

        return $this->render('cart', [
            'cartItems' => $cart,
            'cartTotal' => $this->cartTotal(),
            'cartCount' => $this->cartItemCount(),
        ]);
    }

    // ─── helpers ────────────────────────────────────

    /**
     * @return array<string, array>
     */
    public static function getCart(): array
    {
        return Yii::$app->session->get('cart', []);
    }

    public static function setCart(array $cart): void
    {
        Yii::$app->session->set('cart', $cart);
    }

    public static function cartItemCount(): int
    {
        $count = 0;
        foreach (self::getCart() as $item) {
            $count += (int) ($item['quantity'] ?? 1);
        }
        return $count;
    }

    public static function cartTotal(): float
    {
        $total = 0.0;
        foreach (self::getCart() as $item) {
            $total += (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1);
        }
        return $total;
    }
}
