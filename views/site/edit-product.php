<?php

/** @var yii\web\View $this */
/** @var app\models\ProductEditForm $model */
/** @var array<string, mixed> $brand */
/** @var string $productStatus */

use app\widgets\Alert;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$brandName = (string) ($brand['name'] ?? '');
$brandLogo = trim((string) ($brand['logo'] ?? ''));
$productId = (int) $model->productId;

$this->title = 'Редактировать товар';

$newFilesId = 'seller-edit-new-images';
$this->registerJsFile('@web/js/seller-product-sizes.js', ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerJs('initSellerProductSizes({formName: "ProductEditForm"});', \yii\web\View::POS_END);
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
                radio.name = 'ProductEditForm[mainImageId]';
                radio.value = 'new_' + idx;
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
    <div class="seller-edit-head">
        <h1 class="seller-add-title">Редактировать товар</h1>
        <span class="seller-edit-id">ID: <?= $productId ?></span>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'seller-edit-product-form',
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

    <?= Html::activeHiddenInput($model, 'productId') ?>

    <div class="seller-add-layout seller-edit-layout">
        <div class="seller-add-col seller-add-col--media seller-edit-col--media">
            <div class="seller-edit-panel card-like">
                <div class="seller-add-section-label">Фотографии</div>

                <?php if ($model->existingImages !== []): ?>
                    <div class="seller-edit-gallery" role="list">
                        <?php foreach ($model->existingImages as $img): ?>
                            <?php
                            $imgId = $img instanceof \app\models\ProductImage ? (int) $img->id : (int) ($img['id'] ?? 0);
                            $src = $img instanceof \app\models\ProductImage
                                ? trim((string) $img->image)
                                : trim((string) ($img['image'] ?? ''));
                            $isMain = (int) ($model->mainImageId ?? 0) === $imgId
                                || ((string) $model->mainImageId === (string) $imgId);
                            ?>
                            <div class="seller-edit-gallery-item" role="listitem">
                                <?php if ($src !== ''): ?>
                                    <img
                                        src="<?= Html::encode(Url::to('@web/' . ltrim($src, '/'))) ?>"
                                        alt=""
                                        class="seller-edit-gallery-img"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                <?php else: ?>
                                    <div class="seller-edit-gallery-img seller-edit-gallery-img--empty"></div>
                                <?php endif; ?>
                                <label class="seller-edit-gallery-main">
                                    <input
                                        type="radio"
                                        name="ProductEditForm[mainImageId]"
                                        value="<?= $imgId ?>"
                                        <?= $isMain ? 'checked' : '' ?>
                                    >
                                    <span>Главное</span>
                                </label>
                                <label class="seller-edit-gallery-delete">
                                    <input
                                        type="checkbox"
                                        name="ProductEditForm[deleteImageIds][]"
                                        value="<?= $imgId ?>"
                                    >
                                    <span>Удалить</span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="seller-edit-hint">Нет загруженных фото — добавьте ниже.</p>
                <?php endif; ?>

                <div class="seller-edit-upload-block">
                    <label class="seller-add-section-label" for="<?= Html::encode($newFilesId) ?>">Добавить фото</label>
                    <?= Html::activeFileInput($model, 'newImageFiles', [
                        'id' => $newFilesId,
                        'class' => 'form-control seller-add-input',
                        'accept' => 'image/*',
                        'multiple' => true,
                    ]) ?>
                    <?= Html::error($model, 'newImageFiles', ['class' => 'seller-add-field-error']) ?>
                    <div id="seller-edit-new-preview" class="seller-edit-new-preview"></div>
                    <p class="seller-edit-hint">Можно выбрать несколько файлов. Для новых фото отметьте «Главное» после выбора.</p>
                </div>
            </div>
        </div>

        <div class="seller-add-col seller-add-col--fields">
            <?= $form->field($model, 'name', ['template' => "{input}\n{error}"])->textInput([
                'placeholder' => 'Название товара',
                'autocomplete' => 'off',
            ])->label(false) ?>

            <?= $form->field($model, 'price', ['template' => "{input}\n{error}"])->textInput([
                'placeholder' => 'Цена',
                'inputmode' => 'decimal',
                'autocomplete' => 'off',
            ])->label(false) ?>

            <div class="seller-edit-sizes-block card-like">
                <div class="seller-edit-sizes-head">
                    <div class="seller-add-section-label">Размеры и остаток</div>
                    <button type="button" class="seller-edit-size-add" id="seller-edit-add-size">+ Размер</button>
                </div>
                <div id="seller-edit-sizes" class="seller-edit-sizes">
                    <?php foreach ($model->sizes as $i => $sizeRow): ?>
                        <div class="seller-edit-size-row">
                            <?= Html::hiddenInput(
                                "ProductEditForm[sizes][{$i}][id]",
                                $sizeRow['id'] ?? '',
                                ['data-size-id' => true]
                            ) ?>
                            <?= Html::textInput(
                                "ProductEditForm[sizes][{$i}][size]",
                                $sizeRow['size'] ?? '',
                                [
                                    'class' => 'form-control seller-add-input',
                                    'placeholder' => 'Размер (S, M, 42…)',
                                    'data-size-label' => true,
                                ]
                            ) ?>
                            <?= Html::input(
                                'number',
                                "ProductEditForm[sizes][{$i}][quantity]",
                                $sizeRow['quantity'] ?? 1,
                                [
                                    'class' => 'form-control seller-add-input seller-edit-qty',
                                    'min' => 0,
                                    'step' => 1,
                                    'placeholder' => 'Кол-во',
                                    'data-size-qty' => true,
                                ]
                            ) ?>
                            <button type="button" class="seller-edit-size-remove" data-remove-size aria-label="Удалить размер">×</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?= Html::error($model, 'sizes', ['class' => 'seller-add-field-error']) ?>
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
                <?= $form->field($model, 'description', [
                    'template' => "{input}\n{error}",
                    'labelOptions' => ['class' => 'visually-hidden'],
                ])->textarea([
                    'rows' => 10,
                    'class' => 'form-control seller-add-textarea',
                    'placeholder' => 'Описание товара',
                ])->label(false) ?>
            </div>

            <div class="seller-edit-actions">
                <?= Html::submitButton('Сохранить изменения', ['class' => 'seller-add-submit seller-edit-submit-primary']) ?>
                <a class="seller-edit-link" href="<?= Html::encode(Url::to(['/site/product', 'id' => $productId])) ?>" target="_blank" rel="noopener">Открыть на сайте</a>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <div class="seller-edit-archive-wrap">
        <?php $isActive = ($productStatus ?? 'active') === 'active'; ?>
        <?= Html::beginForm(['seller/toggle-product-status', 'id' => $productId], 'post', ['class' => 'seller-edit-archive-form']) ?>
            <?= Html::submitButton(
                $isActive ? 'Перенести в архив' : 'Вернуть в активные',
                ['class' => 'seller-edit-archive-btn']
            ) ?>
        <?= Html::endForm() ?>
    </div>
</div>
