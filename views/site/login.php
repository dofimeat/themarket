<?php

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Вход';
?>
<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">Вход</h1>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'options' => ['class' => 'auth-form'],
            'fieldConfig' => [
                'template' => "{input}\n{error}",
                'options' => ['class' => 'auth-field-wrap'],
                'errorOptions' => ['class' => 'auth-field-error'],
            ],
        ]); ?>

        <?= $form->field($model, 'email')->textInput([
            'placeholder' => 'E-mail или логин',
            'class' => 'auth-input',
            'autofocus' => true,
            'autocomplete' => 'username',
        ]) ?>

        <?= $form->field($model, 'password')->passwordInput([
            'placeholder' => 'Пароль',
            'class' => 'auth-input',
            'autocomplete' => 'current-password',
        ]) ?>

        <div class="auth-submit-wrap">
            <?= Html::submitButton('Войти', ['class' => 'auth-btn', 'name' => 'login-button']) ?>
        </div>

        <?php ActiveForm::end(); ?>

        <p class="auth-footer">
            Нет аккаунта?
            <a href="<?= Html::encode(Url::to(['/site/register'])) ?>" class="auth-link">Зарегистрироваться</a>
        </p>
    </div>
</div>
