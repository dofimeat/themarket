<?php

/** @var yii\web\View $this */
/** @var app\models\RegisterForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Регистрация';
?>
<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">Регистрация</h1>

        <?php $form = ActiveForm::begin([
            'id' => 'register-form',
            'options' => ['class' => 'auth-form'],
            'fieldConfig' => [
                'template' => "{input}\n{error}",
                'options' => ['class' => 'auth-field-wrap'],
                'errorOptions' => ['class' => 'auth-field-error'],
            ],
        ]); ?>

        <?= $form->field($model, 'first_name')->textInput([
            'placeholder' => 'Имя',
            'class' => 'auth-input',
            'autofocus' => true,
        ]) ?>

        <?= $form->field($model, 'last_name')->textInput([
            'placeholder' => 'Фамилия',
            'class' => 'auth-input',
        ]) ?>

        <?= $form->field($model, 'email')->textInput([
            'placeholder' => 'E-mail',
            'class' => 'auth-input',
            'autocomplete' => 'email',
        ]) ?>

        <?= $form->field($model, 'password')->passwordInput([
            'placeholder' => 'Пароль',
            'class' => 'auth-input',
            'autocomplete' => 'new-password',
        ]) ?>

        <?= $form->field($model, 'password_repeat')->passwordInput([
            'placeholder' => 'Повторите пароль',
            'class' => 'auth-input',
            'autocomplete' => 'new-password',
        ]) ?>

        <div class="auth-submit-wrap">
            <?= Html::submitButton('Зарегистрироваться', ['class' => 'auth-btn auth-btn-register', 'name' => 'register-button']) ?>
        </div>

        <?php ActiveForm::end(); ?>

        <p class="auth-footer">
            Уже есть аккаунт?
            <a href="<?= Html::encode(Url::to(['/site/login'])) ?>" class="auth-link">Войти</a>
        </p>
    </div>
</div>
