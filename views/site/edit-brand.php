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
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file) return;
        preview.src = URL.createObjectURL(file);
    });
})();
JS
);
?>
