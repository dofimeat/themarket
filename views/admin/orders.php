<?php
/** @var yii\web\View $this */
/** @var app\models\Order[] $orders */
/** @var yii\data\Pagination $pages */
/** @var string $statusFilter */
/** @var string $search */

use app\models\Order;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Заказы';

$statuses = [
    Order::STATUS_NEW => 'Новый',
    Order::STATUS_PAID => 'Оплачен',
    Order::STATUS_SHIPPED => 'Отправлен',
    Order::STATUS_COMPLETED => 'Завершён',
    Order::STATUS_CANCELLED => 'Отменён',
];
?>
<div class="admin-wrap">
    <div class="admin-head">
        <h1 class="admin-title">Заказы</h1>
        <a class="admin-back" href="<?= Html::encode(Url::to(['/admin'])) ?>">&larr; Назад</a>
    </div>

    <form class="admin-search-form" action="<?= Html::encode(Url::to(['/admin/orders'])) ?>" method="get">
        <input
            type="text"
            name="search"
            class="admin-search-input"
            placeholder="Поиск по email, имени, телефону..."
            value="<?= Html::encode($search) ?>"
        >
        <button type="submit" class="admin-search-btn">Найти</button>
    </form>

    <div class="admin-filter-pills">
        <a href="<?= Html::encode(Url::to(['/admin/orders'])) ?>"
           class="admin-filter-pill <?= $statusFilter === '' ? 'admin-filter-pill--active' : '' ?>">Все</a>
        <?php foreach ($statuses as $key => $label): ?>
            <a href="<?= Html::encode(Url::to(['/admin/orders', 'status' => $key])) ?>"
               class="admin-filter-pill <?= $statusFilter === $key ? 'admin-filter-pill--active' : '' ?>"><?= Html::encode($label) ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Desktop table -->
    <div class="admin-table-wrap admin-orders-desktop">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Покупатель</th>
                    <th>Телефон</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Оплата</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?= (int) $order->id ?></strong></td>
                        <td>
                            <?= Html::encode($order->first_name . ' ' . $order->last_name) ?><br>
                            <small style="color:#999;"><?= Html::encode($order->email) ?></small>
                        </td>
                        <td><?= Html::encode($order->phone) ?></td>
                        <td><strong><?= number_format((float) $order->total_price, 0, '', ' ') ?> &#8381;</strong></td>
                        <td>
                            <span class="admin-badge admin-badge--<?= Html::encode($order->status) ?>">
                                <?= Html::encode($order->getStatusLabel()) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($order->payment_id)): ?>
                                <span class="admin-badge admin-badge--paid">Оплачен</span>
                            <?php else: ?>
                                <span class="admin-badge admin-badge--new">Ожидает</span>
                            <?php endif; ?>
                        </td>
                        <td><?= Html::encode($order->created_at ?? '—') ?></td>
                        <td class="admin-actions">
                            <a href="<?= Html::encode(Url::to(['/admin/order-view', 'id' => (int) $order->id])) ?>" class="admin-btn admin-btn--edit">Просмотр</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="8" style="text-align:center;color:#999;padding:40px 0;">Заказов не найдено</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile cards -->
    <div class="admin-orders-mobile">
        <?php foreach ($orders as $order): ?>
            <div class="admin-order-card card-like">
                <div class="admin-order-card-head">
                    <span class="admin-order-card-num">#<?= (int) $order->id ?></span>
                    <span class="admin-badge admin-badge--<?= Html::encode($order->status) ?>">
                        <?= Html::encode($order->getStatusLabel()) ?>
                    </span>
                </div>
                <div class="admin-order-card-row">
                    <span class="admin-order-card-label">Покупатель</span>
                    <span><?= Html::encode($order->first_name . ' ' . $order->last_name) ?></span>
                </div>
                <div class="admin-order-card-row">
                    <span class="admin-order-card-label">Телефон</span>
                    <span><?= Html::encode($order->phone) ?></span>
                </div>
                <div class="admin-order-card-row">
                    <span class="admin-order-card-label">Сумма</span>
                    <span><strong><?= number_format((float) $order->total_price, 0, '', ' ') ?> &#8381;</strong></span>
                </div>
                <div class="admin-order-card-row">
                    <span class="admin-order-card-label">Дата</span>
                    <span><?= Html::encode($order->created_at ?? '—') ?></span>
                </div>
                <div class="admin-order-card-foot">
                    <a href="<?= Html::encode(Url::to(['/admin/order-view', 'id' => (int) $order->id])) ?>" class="admin-btn admin-btn--edit">Просмотр</a>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?>
            <div class="admin-order-card card-like" style="text-align:center;color:#999;padding:40px;">
                Заказов не найдено
            </div>
        <?php endif; ?>
    </div>

    <?= LinkPager::widget([
        'pagination' => $pages,
        'options' => ['class' => 'pagination admin-pagination'],
        'linkOptions' => ['class' => 'page-link'],
        'activePageCssClass' => 'active',
        'disabledPageCssClass' => 'disabled',
    ]) ?>
</div>
