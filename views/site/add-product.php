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

$newFilesId = 'seller-add-new-images';
$this->registerJsFile('@web/js/seller-product-sizes.js', ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerJs('initSellerProductSizes({formName: "ProductAddForm"});', \yii\web\View::POS_END);
$this->registerJs('initSellerProductFeatures({formName: "ProductAddForm"});', \yii\web\View::POS_END);
$this->registerJs(<<<JS
(function () {
    var newInput = document.getElementById('{$newFilesId}');
    var newPreview = document.getElementById('seller-edit-new-preview');
    if (newInput && newPreview) {
        newInput.addEventListener('change', function () {
            newPreview.innerHTML = '';
            Array.prototype.forEach.call(newInput.files || [], function (file, idx) {
                if (!file.type.match(/^image\\//)) return;
                var wrap = document.createElement('label');
                wrap.className = 'seller-edit-new-thumb';
                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = '';
                var radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'ProductAddForm[mainImageId]';
                radio.value = 'new_' + idx;
                if (idx === 0) radio.checked = true;
                radio.title = 'Сделать главным';
                wrap.appendChild(img);
                wrap.appendChild(radio);
                newPreview.appendChild(wrap);
            });
        });
    }
})();
JS
);
?>
<div class="seller-add-page seller-edit-page">
    <?= Alert::widget() ?>

    <a href="<?= Html::encode(Url::to(['seller/brand-dashboard'])) ?>" class="seller-add-back">← Панель бренда</a>
    <h1 class="seller-add-title">Добавить товар</h1>

    <?php $form = ActiveForm::begin([
        'id' => 'seller-add-product-form',
        'options' => [
            'class' => 'seller-add-form',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{input}\n{error}",
            'inputOptions' => ['class' => 'form-control seller-add-input'],
            'errorOptions' => ['class' => 'seller-add-field-error'],
        ],
    ]); ?>

    <div class="seller-add-layout seller-edit-layout">
        <div class="seller-add-col seller-add-col--media seller-edit-col--media">
            <div class="seller-edit-panel card-like">
                <div class="seller-add-section-label">Фотографии</div>
                <div class="seller-edit-upload-block">
                    <label class="seller-add-section-label" for="<?= Html::encode($newFilesId) ?>">Загрузите одно или несколько фото</label>
                    <?= Html::activeFileInput($model, 'newImageFiles', [
                        'id' => $newFilesId,
                        'class' => 'form-control seller-add-input',
                        'accept' => 'image/*',
                        'multiple' => true,
                    ]) ?>
                    <?= Html::error($model, 'newImageFiles', ['class' => 'seller-add-field-error']) ?>
                    <div id="seller-edit-new-preview" class="seller-edit-new-preview"></div>
                    <p class="seller-edit-hint">Отметьте «Главное» у нужного превью после выбора файлов.</p>
                </div>
            </div>
        </div>

        <div class="seller-add-col seller-add-col--fields">
            <?= $form->field($model, 'name')->textInput(['placeholder' => 'Название товара', 'autocomplete' => 'off']) ?>
            <?= $form->field($model, 'price')->textInput(['placeholder' => 'Цена', 'inputmode' => 'decimal', 'autocomplete' => 'off']) ?>

            <div class="seller-edit-sizes-block card-like">
                <div class="seller-edit-sizes-head">
                    <div class="seller-add-section-label">Размеры и остаток</div>
                    <button type="button" class="seller-edit-size-add" id="seller-edit-add-size">+ Размер</button>
                </div>
                <div id="seller-edit-sizes" class="seller-edit-sizes">
                    <?php foreach ($model->sizes as $i => $sizeRow): ?>
                        <div class="seller-edit-size-row">
                            <?= Html::hiddenInput(
                                "ProductAddForm[sizes][{$i}][id]",
                                $sizeRow['id'] ?? '',
                                ['data-size-id' => true]
                            ) ?>
                            <?= Html::textInput(
                                "ProductAddForm[sizes][{$i}][size]",
                                $sizeRow['size'] ?? '',
                                [
                                    'class' => 'form-control seller-add-input',
                                    'placeholder' => 'Размер (S, M, 42…)',
                                    'data-size-label' => true,
                                ]
                            ) ?>
                            <?= Html::input(
                                'number',
                                "ProductAddForm[sizes][{$i}][quantity]",
                                $sizeRow['quantity'] ?? 1,
                                [
                                    'class' => 'form-control seller-add-input seller-edit-qty',
                                    'min' => 0,
                                    'step' => 1,
                                    'data-size-qty' => true,
                                ]
                            ) ?>
                            <button type="button" class="seller-edit-size-remove" data-remove-size aria-label="Удалить размер">×</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?= Html::error($model, 'sizes', ['class' => 'seller-add-field-error']) ?>
            </div>

            <div class="seller-edit-sizes-block card-like">
                <div class="seller-edit-sizes-head">
                    <div class="seller-add-section-label">Характеристики</div>
                    <button type="button" class="seller-edit-size-add" id="seller-edit-add-feature">+ Характеристика</button>
                </div>
                <div id="seller-edit-features" class="seller-edit-features">
                    <?php foreach ($model->features as $i => $featRow): ?>
                        <div class="seller-edit-feature-row">
                            <?= Html::hiddenInput(
                                "ProductAddForm[features][{$i}][id]",
                                $featRow['id'] ?? '',
                                ['data-feature-id' => true]
                            ) ?>
                            <?= Html::textInput(
                                "ProductAddForm[features][{$i}][name]",
                                $featRow['name'] ?? '',
                                [
                                    'class' => 'form-control seller-add-input',
                                    'placeholder' => 'Название (Материал, Цвет…)',
                                    'data-feature-name' => true,
                                ]
                            ) ?>
                            <?= Html::textInput(
                                "ProductAddForm[features][{$i}][value]",
                                $featRow['value'] ?? '',
                                [
                                    'class' => 'form-control seller-add-input',
                                    'placeholder' => 'Значение (Хлопок 100%…)',
                                    'data-feature-value' => true,
                                ]
                            ) ?>
                            <button type="button" class="seller-edit-size-remove" data-remove-feature aria-label="Удалить характеристику">×</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?= Html::error($model, 'features', ['class' => 'seller-add-field-error']) ?>
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
                <div class="seller-add-section-label">Описание</div>
                <?= $form->field($model, 'description')->textarea([
                    'rows' => 10,
                    'class' => 'form-control seller-add-textarea',
                    'placeholder' => 'Описание товара',
                ])->label(false) ?>
            </div>

            <div class="seller-edit-actions">
                <?= Html::submitButton('Добавить товар', ['class' => 'seller-add-submit seller-edit-submit-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
