<?php
/** @var yii\web\View $this */
/** @var app\models\Brand $brand */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Редактирование бренда';
?>
<div class="admin-wrap">
    <div class="admin-head">
        <h1 class="admin-title">Редактирование бренда #<?= (int) $brand->id ?></h1>
        <a class="admin-back" href="<?= Html::encode(Url::to(['/admin/brands'])) ?>">← Назад</a>
    </div>

    <div class="admin-form-wrap card-like">
        <form method="post" action="<?= Html::encode(Url::to(['/admin/brand-update', 'id' => (int) $brand->id])) ?>">
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Html::encode(Yii::$app->request->csrfToken) ?>">

            <div class="admin-form-row">
                <label class="admin-label" for="brand-name">Название</label>
                <input type="text" id="brand-name" name="name" class="admin-input" value="<?= Html::encode($brand->name) ?>" required>
            </div>

            <div class="admin-form-row">
                <label class="admin-label" for="brand-city">Город</label>
                <input type="text" id="brand-city" name="city" class="admin-input" value="<?= Html::encode($brand->city ?? '') ?>">
            </div>

            <div class="admin-form-row">
                <label class="admin-label" for="brand-description">Описание</label>
                <textarea id="brand-description" name="description" class="admin-textarea" rows="6"><?= Html::encode($brand->description ?? '') ?></textarea>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn--primary">Сохранить</button>
                <a href="<?= Html::encode(Url::to(['/admin/brands'])) ?>" class="admin-btn">Отмена</a>
            </div>
        </form>
    </div>
</div>
