<?php
/** @var yii\web\View $this */
/** @var app\models\Order $order */

use app\models\Order;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Заказ #' . $order->id;

$statuses = [
    Order::STATUS_NEW => 'Новый',
    Order::STATUS_PAID => 'Оплачен',
    Order::STATUS_SHIPPED => 'Отправлен',
    Order::STATUS_COMPLETED => 'Завершён',
    Order::STATUS_CANCELLED => 'Отменён',
];

$deliveryLabels = [
    'courier' => 'Курьер',
    'pickup' => 'Самовывоз',
    'post' => 'Почта',
];
?>
<div class="admin-wrap">
    <div class="admin-head">
        <h1 class="admin-title">
            Заказ #<?= (int) $order->id ?>
            <span class="admin-badge admin-badge--<?= Html::encode($order->status) ?>" style="font-size:14px;vertical-align:middle;margin-left:8px;">
                <?= Html::encode($order->getStatusLabel()) ?>
            </span>
        </h1>
        <a class="admin-back" href="<?= Html::encode(Url::to(['/admin/orders'])) ?>">&larr; К заказам</a>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="admin-flash admin-flash--success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('danger')): ?>
        <div class="admin-flash admin-flash--danger"><?= Yii::$app->session->getFlash('danger') ?></div>
    <?php endif; ?>

    <div class="admin-order-detail-grid">
        <!-- Customer Info -->
        <div class="admin-order-detail-block card-like">
            <h3 class="admin-order-detail-title">Покупатель</h3>
            <div class="admin-order-detail-row">
                <span class="admin-order-detail-label">Имя</span>
                <span><?= Html::encode($order->first_name . ' ' . $order->last_name) ?></span>
            </div>
            <div class="admin-order-detail-row">
                <span class="admin-order-detail-label">Email</span>
                <span><?= Html::encode($order->email) ?></span>
            </div>
            <div class="admin-order-detail-row">
                <span class="admin-order-detail-label">Телефон</span>
                <span><?= Html::encode($order->phone) ?></span>
            </div>
        </div>

        <!-- Delivery Info -->
        <div class="admin-order-detail-block card-like">
            <h3 class="admin-order-detail-title">Доставка</h3>
            <div class="admin-order-detail-row">
                <span class="admin-order-detail-label">Способ</span>
                <span><?= Html::encode($deliveryLabels[$order->delivery_method] ?? $order->delivery_method) ?></span>
            </div>
            <div class="admin-order-detail-row">
                <span class="admin-order-detail-label">Страна</span>
                <span><?= Html::encode($order->country) ?></span>
            </div>
            <div class="admin-order-detail-row">
                <span class="admin-order-detail-label">Город</span>
                <span><?= Html::encode($order->city) ?></span>
            </div>
            <div class="admin-order-detail-row">
                <span class="admin-order-detail-label">Адрес</span>
                <span><?= Html::encode($order->address) ?></span>
            </div>
            <?php if (!empty($order->postal_code)): ?>
                <div class="admin-order-detail-row">
                    <span class="admin-order-detail-label">Индекс</span>
                    <span><?= Html::encode($order->postal_code) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Order Summary -->
        <div class="admin-order-detail-block card-like">
            <h3 class="admin-order-detail-title">Итого</h3>
            <div class="admin-order-detail-row">
                <span class="admin-order-detail-label">Дата</span>
                <span><?= Html::encode($order->created_at ?? '—') ?></span>
            </div>
            <div class="admin-order-detail-row">
                <span class="admin-order-detail-label">Оплата</span>
                <span>
                    <?php if (!empty($order->payment_id)): ?>
                        <span class="admin-badge admin-badge--paid">Оплачен</span>
                        <small style="color:#999;display:block;margin-top:4px;"><?= Html::encode($order->payment_id) ?></small>
                    <?php else: ?>
                        <span class="admin-badge admin-badge--new">Ожидает</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="admin-order-detail-row">
                <span class="admin-order-detail-label">Доставка</span>
                <span><?= number_format((float) $order->delivery_cost, 0, '', ' ') ?> &#8381;</span>
            </div>
            <div class="admin-order-detail-row admin-order-detail-row--total">
                <span class="admin-order-detail-label">Итого</span>
                <span class="admin-order-detail-total"><?= number_format((float) $order->total_price, 0, '', ' ') ?> &#8381;</span>
            </div>
            <?php if (!empty($order->comment)): ?>
                <div class="admin-order-detail-row" style="margin-top:8px;">
                    <span class="admin-order-detail-label">Комментарий</span>
                    <span><?= Html::encode($order->comment) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Items -->
    <div class="admin-order-items card-like">
        <h3 class="admin-order-detail-title">Товары</h3>

        <!-- Desktop table -->
        <div class="admin-table-wrap admin-order-items-desktop">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Фото</th>
                        <th>Товар</th>
                        <th>Размер</th>
                        <th>Цена</th>
                        <th>Кол-во</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order->items as $item): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item->product_image)): ?>
                                    <img src="<?= Html::encode(Url::to('@web/' . ltrim($item->product_image, '/'))) ?>"
                                         alt="<?= Html::encode($item->product_name) ?>"
                                         style="width:48px;height:48px;object-fit:cover;border-radius:6px;"
                                         loading="lazy">
                                <?php else: ?>
                                    <div style="width:48px;height:48px;background:#f0f0f0;border-radius:6px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><?= Html::encode($item->product_name) ?></td>
                            <td><?= Html::encode($item->size ?: '—') ?></td>
                            <td><?= number_format((float) $item->price, 0, '', ' ') ?> &#8381;</td>
                            <td><?= (int) $item->quantity ?></td>
                            <td><strong><?= number_format((float) $item->total, 0, '', ' ') ?> &#8381;</strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile cards for items -->
        <div class="admin-order-items-mobile">
            <?php foreach ($order->items as $item): ?>
                <div class="admin-order-item-card">
                    <div class="admin-order-item-img">
                        <?php if (!empty($item->product_image)): ?>
                            <img src="<?= Html::encode(Url::to('@web/' . ltrim($item->product_image, '/'))) ?>"
                                 alt="<?= Html::encode($item->product_name) ?>"
                                 loading="lazy">
                        <?php endif; ?>
                    </div>
                    <div class="admin-order-item-info">
                        <div class="admin-order-item-name"><?= Html::encode($item->product_name) ?></div>
                        <div class="admin-order-item-meta">
                            Размер: <?= Html::encode($item->size ?: '—') ?> &middot;
                            <?= (int) $item->quantity ?> шт.
                        </div>
                        <div class="admin-order-item-price">
                            <?= number_format((float) $item->total, 0, '', ' ') ?> &#8381;
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Status Change Buttons -->
    <div class="admin-order-status-actions card-like">
        <h3 class="admin-order-detail-title">Изменить статус</h3>
        <div class="admin-order-status-btns">
            <?php foreach ($statuses as $key => $label): ?>
                <?php if ($key !== $order->status): ?>
                    <a href="<?= Html::encode(Url::to(['/admin/order-status', 'id' => (int) $order->id, 'status' => $key])) ?>"
                       class="admin-btn admin-btn--<?= $key === 'cancelled' ? 'delete' : ($key === 'completed' ? 'ok' : 'primary') ?>"
                       data-confirm="Изменить статус на «<?= $label ?>»?">
                        <?= Html::encode($label) ?>
                    </a>
                <?php else: ?>
                    <span class="admin-btn admin-btn--secondary" style="opacity:0.5;cursor:default;">
                        <?= Html::encode($label) ?> (текущий)
                    </span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
