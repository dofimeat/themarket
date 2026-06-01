<?php
/** @var yii\web\View $this */
/** @var app\models\Brand[] $brands */
/** @var yii\data\Pagination $pages */
/** @var string $statusFilter */
/** @var string $search */

use app\models\Brand;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Бренды';
$statusLabels = [
    Brand::STATUS_PENDING => 'Ожидает',
    Brand::STATUS_APPROVED => 'Одобрен',
    Brand::STATUS_REJECTED => 'Отклонён',
];
?>
<div class="admin-wrap">
    <div class="admin-head">
        <h1 class="admin-title">Бренды</h1>
        <a class="admin-back" href="<?= Html::encode(Url::to(['/admin'])) ?>">← Назад</a>
    </div>

    <div class="admin-filters">
        <form class="admin-search-form" action="<?= Html::encode(Url::to(['/admin/brands'])) ?>" method="get">
            <input type="text" name="search" class="admin-search-input" placeholder="Поиск по названию..." value="<?= Html::encode($search) ?>">
            <select name="status" class="admin-select admin-select--filter">
                <option value="">Все статусы</option>
                <option value="<?= Brand::STATUS_PENDING ?>" <?= $statusFilter === Brand::STATUS_PENDING ? 'selected' : '' ?>>Ожидает</option>
                <option value="<?= Brand::STATUS_APPROVED ?>" <?= $statusFilter === Brand::STATUS_APPROVED ? 'selected' : '' ?>>Одобрен</option>
                <option value="<?= Brand::STATUS_REJECTED ?>" <?= $statusFilter === Brand::STATUS_REJECTED ? 'selected' : '' ?>>Отклонён</option>
            </select>
            <button type="submit" class="admin-search-btn">Фильтр</button>
        </form>
    </div>

    <div class="admin-brand-grid">
        <?php foreach ($brands as $brand):
            $owner = $brand->user_id ? \app\models\User::findOne($brand->user_id) : null;
            $logoUrl = Brand::resolveLogoPath($brand->logo ?? null);
        ?>
            <div class="admin-brand-card card-like <?= $brand->isBlocked() ? 'admin-brand-card--blocked' : '' ?>">
                <div class="admin-brand-card-top">
                    <img src="<?= Html::encode(Url::to('@web/' . ltrim($logoUrl, '/'))) ?>" alt="" class="admin-brand-card-logo">
                    <div class="admin-brand-card-meta">
                        <h3 class="admin-brand-card-name"><?= Html::encode($brand->name) ?></h3>
                        <?php if (!empty($brand->city)): ?>
                            <p class="admin-brand-card-city"><?= Html::encode($brand->city) ?></p>
                        <?php endif; ?>
                        <p class="admin-brand-card-owner"><?= $owner ? Html::encode($owner->email) : '—' ?></p>
                    </div>
                </div>
                <div class="admin-brand-card-badges">
                    <span class="admin-badge admin-badge--<?= Html::encode($brand->status ?? '') ?>">
                        <?= Html::encode($statusLabels[$brand->status] ?? ($brand->status ?? '—')) ?>
                    </span>
                    <?php if ($brand->isBlocked()): ?>
                        <span class="admin-badge admin-badge--danger">Заблокирован</span>
                    <?php endif; ?>
                </div>
                <div class="admin-brand-card-actions">
                    <?php if ($brand->status === Brand::STATUS_PENDING): ?>
                        <a href="<?= Html::encode(Url::to(['/admin/brand-approve', 'id' => (int) $brand->id])) ?>" class="admin-btn admin-btn--ok">Одобрить</a>
                        <a href="<?= Html::encode(Url::to(['/admin/brand-reject', 'id' => (int) $brand->id])) ?>" class="admin-btn admin-btn--warning">Отклонить</a>
                    <?php endif; ?>
                    <a href="<?= Html::encode(Url::to(['/admin/brand-update', 'id' => (int) $brand->id])) ?>" class="admin-btn admin-btn--edit">Редактировать</a>
                    <a href="<?= Html::encode(Url::to(['/admin/brand-block', 'id' => (int) $brand->id])) ?>" class="admin-btn <?= $brand->isBlocked() ? 'admin-btn--ok' : 'admin-btn--warning' ?>">
                        <?= $brand->isBlocked() ? 'Разблокировать' : 'Заблокировать' ?>
                    </a>
                    <a href="<?= Html::encode(Url::to(['/admin/brand-delete', 'id' => (int) $brand->id])) ?>" class="admin-btn admin-btn--delete" data-confirm="Удалить бренд?">Удалить</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?= LinkPager::widget([
        'pagination' => $pages,
        'options' => ['class' => 'pagination admin-pagination'],
        'linkOptions' => ['class' => 'page-link'],
        'activePageCssClass' => 'active',
        'disabledPageCssClass' => 'disabled',
    ]) ?>
</div>
