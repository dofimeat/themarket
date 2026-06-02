<?php
/** @var yii\web\View $this */
/** @var app\models\Order $order */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Заказ #' . $order->id . ' оформлен';
?>
<div class="home-wrap">
    <div class="order-success-wrap">
        <div class="order-success-icon">✓</div>
        <h1 class="order-success-title">Заказ оформлен!</h1>
        <div class="order-success-text">
            Номер заказа: <strong>#<?= $order->id ?></strong>
        </div>
        <div class="order-success-text">
            Мы отправили подтверждение на <strong><?= Html::encode($order->email) ?></strong>
        </div>

        <div class="order-success-details card-like">
            <div class="order-success-detail-row">
                <span class="order-success-label">Статус</span>
                <span class="order-success-value"><?= Html::encode($order->getStatusLabel()) ?></span>
            </div>
            <div class="order-success-detail-row">
                <span class="order-success-label">Сумма</span>
                <span class="order-success-value"><?= number_format((float) $order->total_price, 0, '', ' ') ?> ₽</span>
            </div>
            <div class="order-success-detail-row">
                <span class="order-success-label">Оплата</span>
                <span class="order-success-value">Банковская карта</span>
            </div>
            <div class="order-success-detail-row">
                <span class="order-success-label">Доставка</span>
                <span class="order-success-value"><?= Html::encode($order->city . ', ' . $order->address) ?></span>
            </div>
        </div>

        <div class="order-success-actions">
            <a href="<?= Html::encode(Url::to(['/site/catalog'])) ?>" class="order-success-btn">Продолжить покупки</a>
        </div>
    </div>
</div>
