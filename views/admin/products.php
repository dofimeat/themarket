<?php
/** @var yii\web\View $this */
/** @var array[] $products */
/** @var yii\data\Pagination $pages */
/** @var string $statusFilter */
/** @var string $search */

use app\models\Product;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Товары';

$statusLabels = [
    Product::STATUS_DRAFT => 'Черновик',
    Product::STATUS_PENDING => 'На модерации',
    Product::STATUS_PUBLISHED => 'Опубликован',
    Product::STATUS_REJECTED => 'Отклонён',
];

$statusBadgeClass = [
    Product::STATUS_DRAFT => 'draft',
    Product::STATUS_PENDING => 'pending',
    Product::STATUS_PUBLISHED => 'approved',
    Product::STATUS_REJECTED => 'rejected',
];
?>
<div class="admin-wrap">
    <div class="admin-head">
        <h1 class="admin-title">Товары</h1>
        <a class="admin-back" href="<?= Html::encode(Url::to(['/admin'])) ?>">← Назад</a>
    </div>

    <div class="admin-filters">
        <form class="admin-search-form" action="<?= Html::encode(Url::to(['/admin/products'])) ?>" method="get">
            <input type="text" name="search" class="admin-search-input" placeholder="Поиск по названию..." value="<?= Html::encode($search) ?>">
            <select name="status" class="admin-select admin-select--filter">
                <option value="">Все статусы</option>
                <option value="<?= Product::STATUS_DRAFT ?>" <?= $statusFilter === Product::STATUS_DRAFT ? 'selected' : '' ?>>Черновик</option>
                <option value="<?= Product::STATUS_PENDING ?>" <?= $statusFilter === Product::STATUS_PENDING ? 'selected' : '' ?>>На модерации</option>
                <option value="<?= Product::STATUS_PUBLISHED ?>" <?= $statusFilter === Product::STATUS_PUBLISHED ? 'selected' : '' ?>>Опубликован</option>
                <option value="<?= Product::STATUS_REJECTED ?>" <?= $statusFilter === Product::STATUS_REJECTED ? 'selected' : '' ?>>Отклонён</option>
            </select>
            <button type="submit" class="admin-search-btn">Фильтр</button>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Фото</th>
                    <th>Название</th>
                    <th>Бренд</th>
                    <th>Цена</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= (int) $p['id'] ?></td>
                        <td>
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= Html::encode(Url::to('@web/' . ltrim((string) $p['image'], '/'))) ?>" alt="" class="admin-product-thumb">
                            <?php else: ?>
                                <div class="admin-product-thumb admin-product-thumb--empty"></div>
                            <?php endif; ?>
                        </td>
                        <td><?= Html::encode($p['name']) ?></td>
                        <td><?= Html::encode($p['brand_name'] ?? '—') ?></td>
                        <td><?= number_format((float) $p['price'], 0, '.', ' ') ?> ₽</td>
                        <td>
                            <span class="admin-badge admin-badge--<?= Html::encode($statusBadgeClass[$p['status']] ?? $p['status']) ?>">
                                <?= Html::encode($statusLabels[$p['status']] ?? $p['status']) ?>
                            </span>
                        </td>
                        <td class="admin-actions">
                            <a href="<?= Html::encode(Url::to(['/admin/product-update', 'id' => (int) $p['id']])) ?>" class="admin-btn admin-btn--edit">Редактировать</a>
                            <div class="admin-dropdown">
                                <button type="button" class="admin-btn admin-btn--secondary">Статус ▼</button>
                                <div class="admin-dropdown-menu">
                                    <a href="<?= Html::encode(Url::to(['/admin/product-status', 'id' => (int) $p['id'], 'status' => Product::STATUS_DRAFT])) ?>">Черновик</a>
                                    <a href="<?= Html::encode(Url::to(['/admin/product-status', 'id' => (int) $p['id'], 'status' => Product::STATUS_PENDING])) ?>">На модерации</a>
                                    <a href="<?= Html::encode(Url::to(['/admin/product-status', 'id' => (int) $p['id'], 'status' => Product::STATUS_PUBLISHED])) ?>">Опубликовать</a>
                                    <a href="<?= Html::encode(Url::to(['/admin/product-status', 'id' => (int) $p['id'], 'status' => Product::STATUS_REJECTED])) ?>">Отклонить</a>
                                </div>
                            </div>
                            <a href="<?= Html::encode(Url::to(['/admin/product-delete', 'id' => (int) $p['id']])) ?>" class="admin-btn admin-btn--delete" data-confirm="Удалить товар?">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?= LinkPager::widget([
        'pagination' => $pages,
        'options' => ['class' => 'pagination admin-pagination'],
        'linkOptions' => ['class' => 'page-link'],
        'activePageCssClass' => 'active',
        'disabledPageCssClass' => 'disabled',
    ]) ?>
</div>
