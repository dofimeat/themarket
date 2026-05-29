<?php

/** @var yii\web\View $this */
/** @var app\models\ProductAddForm $model */
/** @var array<string, mixed> $brand */

use app\widgets\Alert;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$brandName = (string) ($brand['name'] ?? '');
$brandLogo = trim((string) ($brand['logo'] ?? ''));

$this->title = 'Добавить товар';

$imageInputId = 'seller-product-image';
$this->registerJs(<<<JS
(function () {
    var input = document.getElementById('{$imageInputId}');
    var preview = document.getElementById('seller-add-preview');
    var plus = document.getElementById('seller-add-plus');
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        var f = input.files && input.files[0];
        if (!f || !f.type.match(/^image\\//)) return;
        preview.src = URL.createObjectURL(f);
        preview.removeAttribute('hidden');
        preview.style.display = 'block';
        if (plus) plus.style.visibility = 'hidden';
    });
})();
JS
);
?>
<div class="seller-add-page">
    <?= Alert::widget() ?>

    <a href="<?= Html::encode(Url::to(['/site/brand-dashboard'])) ?>" class="seller-add-back">← Панель бренда</a>
    <h1 class="seller-add-title">Добавить товар</h1>

    <?php $form = ActiveForm::begin([
        'id' => 'seller-add-product-form',
        'options' => [
            'class' => 'seller-add-form',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}",
            'labelOptions' => ['class' => 'seller-add-field-label'],
            'inputOptions' => ['class' => 'form-control seller-add-input'],
            'errorOptions' => ['class' => 'seller-add-field-error'],
        ],
    ]); ?>

    <div class="seller-add-layout">
        <div class="seller-add-col seller-add-col--media">
            <div class="seller-add-media-box card-like">
                <?= Html::activeFileInput($model, 'imageFile', [
                    'id' => $imageInputId,
                    'class' => 'seller-add-file-native visually-hidden',
                    'accept' => 'image/*',
                ]) ?>
                <label for="<?= Html::encode($imageInputId) ?>" class="seller-add-dropzone">
                    <span class="seller-add-plus" id="seller-add-plus" aria-hidden="true">+</span>
                    <img id="seller-add-preview" class="seller-add-preview-img" alt="" width="600" height="800" hidden>
                </label>
                <?= Html::error($model, 'imageFile', ['class' => 'seller-add-field-error seller-add-image-error']) ?>
                <div class="seller-add-dots" aria-hidden="true">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <span class="seller-add-dot <?= $i === 0 ? 'is-active' : '' ?>"></span>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <div class="seller-add-col seller-add-col--fields">
            <?= $form->field($model, 'name', ['template' => "{input}\n{error}"])->textInput([
                'placeholder' => 'Введите название',
                'autocomplete' => 'off',
            ])->label(false) ?>

            <?= $form->field($model, 'price', ['template' => "{input}\n{error}"])->textInput([
                'placeholder' => 'Введите цену',
                'inputmode' => 'decimal',
                'autocomplete' => 'off',
            ])->label(false) ?>

            <?= $form->field($model, 'size', ['template' => "{input}\n{error}"])->textInput([
                'placeholder' => 'Размер товара',
                'autocomplete' => 'off',
            ])->label(false) ?>

            <div class="seller-add-submit-wrap">
                <?= Html::submitButton('Добавить товар', ['class' => 'seller-add-submit']) ?>
            </div>

            <div class="seller-add-brand-box card-like">
                <?php if ($brandLogo !== ''): ?>
                    <div class="seller-add-brand-logo-wrap">
                        <img src="<?= Html::encode(Url::to('@web/' . ltrim($brandLogo, '/'))) ?>" alt="" class="seller-add-brand-logo" width="40" height="40" loading="lazy" decoding="async">
                    </div>
                <?php else: ?>
                    <div class="seller-add-brand-logo-fallback" aria-hidden="true"></div>
                <?php endif; ?>
                <span class="seller-add-brand-name"><?= Html::encode($brandName !== '' ? $brandName : 'Бренд') ?></span>
            </div>

            <div class="seller-add-desc-block">
                <div class="seller-add-section-label">Описание:</div>
                <?= $form->field($model, 'description', [
                    'template' => "{input}\n{error}",
                    'labelOptions' => ['class' => 'visually-hidden'],
                ])->textarea([
                    'rows' => 8,
                    'class' => 'form-control seller-add-textarea',
                    'placeholder' => 'Описание товара',
                ])->label(false) ?>
            </div>

            <div class="seller-add-specs-block">
                <div class="seller-add-section-label">Характеристики:</div>
                <ul class="seller-add-specs-list">
                    <li>Материал: 100% Хлопок / Техническая ткань</li>
                    <li>Цвет: Белый / Антрацит</li>
                    <li>Ручная работа</li>
                    <li>Пол: унисекс</li>
                </ul>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
