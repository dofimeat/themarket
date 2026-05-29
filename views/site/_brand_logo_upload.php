<?php

/** @var yii\widgets\ActiveForm $form */
/** @var app\models\BrandRegisterForm $model */
/** @var string $previewId */

use yii\helpers\Html;
use yii\helpers\Url;

$previewId = $previewId ?? 'brand-logo-preview';
$logoPath = $model->currentLogo !== ''
    ? $model->currentLogo
    : \app\models\User::DEFAULT_AVATAR;
$logoUrl = Url::to('@web/' . ltrim($logoPath, '/'));
?>
<div class="seller-brand-logo-upload">
    <img
        src="<?= Html::encode($logoUrl) ?>"
        alt=""
        class="seller-brand-logo-upload-preview"
        width="96"
        height="96"
        id="<?= Html::encode($previewId) ?>"
        loading="lazy"
        decoding="async"
    >
    <?= $form->field($model, 'logoFile', [
        'options' => ['class' => 'seller-brand-logo-file-field'],
    ])->fileInput([
        'class' => 'seller-brand-input seller-brand-input--file',
        'accept' => 'image/jpeg,image/png,image/gif,image/webp',
        'id' => $previewId . '-file',
    ]) ?>
    <p class="seller-brand-logo-hint">Логотип бренда: JPG, PNG, GIF или WebP, до 5 МБ</p>
</div>
