<?php

/** @var yii\web\View $this */
/** @var app\models\BrandRegisterForm $model */

use app\widgets\Alert;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Регистрация бренда';
?>
<div class="seller-brand-page">
    <?= Alert::widget() ?>
    <div class="seller-brand-card card-like">
        <h1 class="seller-brand-title">Регистрация бренда</h1>
        <p class="seller-brand-lead">Укажите данные бренда. После сохранения вы сможете управлять витриной как продавец.</p>

        <?php $form = ActiveForm::begin([
            'id' => 'register-brand-form',
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
            'previewId' => 'register-brand-logo-preview',
        ]) ?>

        <?= $form->field($model, 'name')->textInput([
            'autofocus' => true,
            'autocomplete' => 'organization',
        ]) ?>

        <?= $form->field($model, 'city')->textInput([
            'autocomplete' => 'address-level2',
        ]) ?>

        <?= $form->field($model, 'description')->textarea([
            'rows' => 6,
            'class' => 'seller-brand-textarea',
            'placeholder' => 'Расскажите о концепции и истории бренда',
        ]) ?>

        <div class="seller-brand-actions">
            <?= Html::submitButton('Зарегистрировать бренд', ['class' => 'seller-brand-submit']) ?>
            <a class="seller-brand-back" href="<?= Html::encode(Url::to(['/site/profile'])) ?>">Назад в профиль</a>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
$this->registerJs(<<<'JS'
(function () {
    var input = document.getElementById('register-brand-logo-preview-file');
    var preview = document.getElementById('register-brand-logo-preview');
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
