<?php
/** @var yii\web\View $this */
/** @var int $userCount */
/** @var int $brandCount */
/** @var int $productCount */
/** @var int $pendingBrands */
/** @var int $pendingProducts */
/** @var int $orderCount */
/** @var int $pendingOrders */
/** @var int $reviewCount */
/** @var int $pendingReviews */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Админ-панель';
?>
<div class="admin-wrap">
    <h1 class="admin-title">Админ-панель</h1>

    <div class="admin-stats">
        <a href="<?= Html::encode(Url::to(['/admin/users'])) ?>" class="admin-stat card-like">
            <div class="admin-stat-value"><?= (int) $userCount ?></div>
            <div class="admin-stat-label">Пользователи</div>
        </a>
        <a href="<?= Html::encode(Url::to(['/admin/brands'])) ?>" class="admin-stat card-like">
            <div class="admin-stat-value"><?= (int) $brandCount ?></div>
            <div class="admin-stat-label">Бренды</div>
        </a>
        <a href="<?= Html::encode(Url::to(['/admin/products'])) ?>" class="admin-stat card-like">
            <div class="admin-stat-value"><?= (int) $productCount ?></div>
            <div class="admin-stat-label">Товары</div>
        </a>
        <a href="<?= Html::encode(Url::to(['/admin/orders'])) ?>" class="admin-stat card-like">
            <div class="admin-stat-value"><?= (int) $orderCount ?></div>
            <div class="admin-stat-label">Заказы</div>
        </a>
        <a href="<?= Html::encode(Url::to(['/admin/reviews'])) ?>" class="admin-stat card-like">
            <div class="admin-stat-value"><?= (int) $reviewCount ?></div>
            <div class="admin-stat-label">Отзывы</div>
        </a>
    </div>

    <div class="admin-alerts">
        <?php if ($pendingOrders > 0): ?>
            <a href="<?= Html::encode(Url::to(['/admin/orders', 'status' => 'new'])) ?>" class="admin-alert admin-alert--warning card-like">
                <span class="admin-alert-count"><?= (int) $pendingOrders ?></span>
                <span class="admin-alert-text"><?= (int) $pendingOrders === 1 ? 'заказ ожидает обработки' : 'заказов ожидают обработки' ?></span>
            </a>
        <?php endif; ?>
        <?php if ($pendingReviews > 0): ?>
            <a href="<?= Html::encode(Url::to(['/admin/reviews', 'status' => 'pending'])) ?>" class="admin-alert admin-alert--warning card-like">
                <span class="admin-alert-count"><?= (int) $pendingReviews ?></span>
                <span class="admin-alert-text"><?= (int) $pendingReviews === 1 ? 'отзыв ожидает модерации' : 'отзывов ожидают модерации' ?></span>
            </a>
        <?php endif; ?>
        <?php if ($pendingBrands > 0): ?>
            <a href="<?= Html::encode(Url::to(['/admin/brands', 'status' => 'pending'])) ?>" class="admin-alert admin-alert--warning card-like">
                <span class="admin-alert-count"><?= (int) $pendingBrands ?></span>
                <span class="admin-alert-text"><?= (int) $pendingBrands === 1 ? 'бренд ожидает модерации' : 'брендов ожидают модерации' ?></span>
            </a>
        <?php endif; ?>
        <?php if ($pendingProducts > 0): ?>
            <a href="<?= Html::encode(Url::to(['/admin/products', 'status' => 'pending'])) ?>" class="admin-alert admin-alert--warning card-like">
                <span class="admin-alert-count"><?= (int) $pendingProducts ?></span>
                <span class="admin-alert-text"><?= (int) $pendingProducts === 1 ? 'товар ожидает модерации' : 'товаров ожидают модерации' ?></span>
            </a>
        <?php endif; ?>
        <?php if ($pendingBrands === 0 && $pendingProducts === 0 && $pendingOrders === 0 && $pendingReviews === 0): ?>
            <div class="admin-alert admin-alert--ok card-like">
                <span class="admin-alert-text">Всё проверено, новых заявок нет.</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-nav-grid">
        <a href="<?= Html::encode(Url::to(['/admin/orders'])) ?>" class="admin-nav-item card-like">
            <h3 class="admin-nav-title">Заказы</h3>
            <p class="admin-nav-desc">Просмотр заказов, управление статусами и данными доставки.</p>
        </a>
        <a href="<?= Html::encode(Url::to(['/admin/users'])) ?>" class="admin-nav-item card-like">
            <h3 class="admin-nav-title">Пользователи</h3>
            <p class="admin-nav-desc">Просмотр, редактирование ролей, блокировка и удаление.</p>
        </a>
        <a href="<?= Html::encode(Url::to(['/admin/brands'])) ?>" class="admin-nav-item card-like">
            <h3 class="admin-nav-title">Бренды</h3>
            <p class="admin-nav-desc">Модерация, редактирование, блокировка и удаление.</p>
        </a>
        <a href="<?= Html::encode(Url::to(['/admin/products'])) ?>" class="admin-nav-item card-like">
            <h3 class="admin-nav-title">Товары</h3>
            <p class="admin-nav-desc">Модерация, редактирование, изменение статуса и удаление.</p>
        </a>
        <a href="<?= Html::encode(Url::to(['/admin/reviews'])) ?>" class="admin-nav-item card-like">
            <h3 class="admin-nav-title">Отзывы</h3>
            <p class="admin-nav-desc">Модерация отзывов: одобрение, отклонение и удаление.</p>
        </a>
    </div>
</div>
