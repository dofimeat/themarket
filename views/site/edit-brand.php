<?php

/** @var yii\web\View $this */
/** @var app\models\BrandRegisterForm $model */

use app\models\User;
use app\widgets\Alert;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Редактирование бренда';
?>
<div class="seller-brand-page">
    <?= Alert::widget() ?>
    <div class="seller-brand-card card-like">
        <h1 class="seller-brand-title">Редактирование бренда</h1>
        <p class="seller-brand-lead">Измените данные бренда и сохраните.</p>

        <?php $form = ActiveForm::begin([
            'id' => 'edit-brand-form',
            'action' => Url::to(['seller/edit-brand']),
            'options' => [
                'class' => 'seller-brand-form',
                'enctype' => 'multipart/form-data',
            ],
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'seller-brand-label'],
                'inputOptions' => ['class' => 'seller-brand-input'],
                'errorOptions' => ['class' => 'seller-brand-error'],
            ],
        ]); ?>

        <?= $this->render('_brand_logo_upload', [
            'form' => $form,
            'model' => $model,
            'previewId' => 'edit-brand-logo-preview',
        ]) ?>

        <div class="seller-brand-banner-section">
            <div class="seller-brand-banner-upload">
                <label class="seller-brand-label" for="edit-brand-banner-preview">Баннер бренда</label>
                <?php if ($model->currentBannerImage !== ''): ?>
                    <img
                        src="<?= Html::encode(Url::to('@web/' . ltrim($model->currentBannerImage, '/'))) ?>"
                        alt="Текущий баннер"
                        class="seller-brand-banner-preview"
                        id="edit-brand-banner-preview"
                        loading="lazy"
                    >
                <?php else: ?>
                    <div class="seller-brand-banner-placeholder" id="edit-brand-banner-preview">Баннер не задан</div>
                <?php endif; ?>
                <?= $form->field($model, 'bannerImageFile', [
                    'options' => ['class' => 'seller-brand-logo-file-field'],
                ])->fileInput([
                    'class' => 'seller-brand-input seller-brand-input--file',
                    'accept' => 'image/jpeg,image/png,image/gif,image/webp',
                    'id' => 'edit-brand-banner-preview-file',
                ]) ?>
                <p class="seller-brand-logo-hint">Изображение баннера: JPG, PNG, GIF или WebP, до 5 МБ</p>
                <?php if ($model->currentBannerImage !== ''): ?>
                    <label class="seller-brand-delete-banner">
                        <input type="checkbox" name="BrandRegisterForm[deleteBanner]" value="1" id="delete-banner-checkbox">
                        Удалить баннер
                    </label>
                <?php endif; ?>
            </div>

            <div class="seller-brand-banner-color-section">
                <label class="seller-brand-label">Цвет фона баннера</label>
                <p class="seller-brand-logo-hint">Выберите цвет, если нет баннера-картинки</p>
                <div class="seller-brand-color-picker-wrap">
                    <input type="color" id="banner-color-picker" value="<?= Html::encode($model->bannerColor !== '' ? $model->bannerColor : '#5a5554') ?>" class="seller-brand-color-picker">
                    <?= $form->field($model, 'bannerColor', [
                        'options' => ['class' => 'seller-brand-color-hex-field'],
                        'template' => "{input}",
                    ])->textInput([
                        'id' => 'banner-color-hex',
                        'class' => 'seller-brand-input seller-brand-input--color',
                        'placeholder' => '#5a5554',
                        'maxlength' => 7,
                    ]) ?>
                </div>
            </div>
        </div>

        <?= $form->field($model, 'name')->textInput([
            'autocomplete' => 'organization',
        ]) ?>

        <?= $form->field($model, 'city')->textInput([
            'autocomplete' => 'address-level2',
        ]) ?>

        <?= $form->field($model, 'description')->textarea([
            'rows' => 6,
            'class' => 'seller-brand-textarea',
            'placeholder' => 'Описание и концепция бренда',
        ]) ?>

        <div class="seller-brand-actions">
            <?= Html::submitButton('Сохранить изменения', ['class' => 'seller-brand-submit']) ?>
            <a class="seller-brand-back" href="<?= Html::encode(Url::to(['seller/brand-dashboard'])) ?>">Назад в панель</a>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
$this->registerJs(<<<'JS'
(function () {
    var input = document.getElementById('edit-brand-logo-preview-file');
    var preview = document.getElementById('edit-brand-logo-preview');
    if (input && preview) {
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            preview.src = URL.createObjectURL(file);
        });
    }

    // Banner image preview
    var bannerInput = document.getElementById('edit-brand-banner-preview-file');
    var bannerPreview = document.getElementById('edit-brand-banner-preview');
    if (bannerInput && bannerPreview) {
        bannerInput.addEventListener('change', function () {
            var file = bannerInput.files && bannerInput.files[0];
            if (!file) return;
            var url = URL.createObjectURL(file);
            if (bannerPreview.tagName === 'IMG') {
                bannerPreview.src = url;
            } else {
                var img = document.createElement('img');
                img.src = url;
                img.alt = 'Превью баннера';
                img.className = 'seller-brand-banner-preview';
                img.id = 'edit-brand-banner-preview';
                bannerPreview.replaceWith(img);
            }
        });
    }

    // Color picker <-> hex input sync
    var colorPicker = document.getElementById('banner-color-picker');
    var colorHex = document.getElementById('banner-color-hex');
    if (colorPicker && colorHex) {
        colorPicker.addEventListener('input', function () {
            colorHex.value = colorPicker.value;
        });
        colorHex.addEventListener('input', function () {
            var v = colorHex.value.trim();
            if (/^#[0-9a-fA-F]{6}$/.test(v)) {
                colorPicker.value = v;
            }
        });
    }

    // Delete banner checkbox
    var deleteCb = document.getElementById('delete-banner-checkbox');
    var bannerPreviewEl = document.getElementById('edit-brand-banner-preview');
    if (deleteCb && bannerPreviewEl) {
        deleteCb.addEventListener('change', function () {
            if (deleteCb.checked) {
                if (bannerPreviewEl.tagName === 'IMG') {
                    bannerPreviewEl.style.opacity = '0.3';
                }
            } else {
                if (bannerPreviewEl.tagName === 'IMG') {
                    bannerPreviewEl.style.opacity = '1';
                }
            }
        });
    }
})();
JS
);
?>
