<?php
/** @var yii\web\View $this */
/** @var app\models\Product $product */

use app\models\Product;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Редактирование товара';

$statusLabels = [
    Product::STATUS_DRAFT => 'Черновик',
    Product::STATUS_PENDING => 'На модерации',
    Product::STATUS_PUBLISHED => 'Опубликован',
    Product::STATUS_REJECTED => 'Отклонён',
];
?>
<div class="admin-wrap">
    <div class="admin-head">
        <h1 class="admin-title">Редактирование товара #<?= (int) $product->id ?></h1>
        <a class="admin-back" href="<?= Html::encode(Url::to(['/admin/products'])) ?>">← Назад</a>
    </div>

    <div class="admin-form-wrap card-like">
        <form method="post" action="<?= Html::encode(Url::to(['/admin/product-update', 'id' => (int) $product->id])) ?>">
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Html::encode(Yii::$app->request->csrfToken) ?>">

            <div class="admin-form-row">
                <label class="admin-label">Название</label>
                <input type="text" name="name" class="admin-input" value="<?= Html::encode($product->name) ?>" required>
            </div>

            <div class="admin-form-row">
                <label class="admin-label">Описание</label>
                <textarea name="description" class="admin-textarea" rows="6"><?= Html::encode($product->description ?? '') ?></textarea>
            </div>

            <div class="admin-form-row">
                <label class="admin-label">Цена</label>
                <input type="text" name="price" class="admin-input" value="<?= Html::encode($product->price) ?>" required>
            </div>

            <div class="admin-form-row">
                <label class="admin-label">Текущий статус</label>
                <div class="admin-static">
                    <span class="admin-badge admin-badge--<?= Html::encode($product->status) ?>">
                        <?= Html::encode($statusLabels[$product->status] ?? $product->status) ?>
                    </span>
                </div>
                <p class="admin-hint">Чтобы изменить статус, вернитесь к списку товаров и используйте кнопку «Статус».</p>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn--primary">Сохранить</button>
                <a href="<?= Html::encode(Url::to(['/admin/products'])) ?>" class="admin-btn">Отмена</a>
            </div>
        </form>
    </div>
</div>
