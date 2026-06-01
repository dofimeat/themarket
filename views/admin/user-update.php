<?php
/** @var yii\web\View $this */
/** @var app\models\User $user */

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Редактирование пользователя';
?>
<div class="admin-wrap">
    <div class="admin-head">
        <h1 class="admin-title">Редактирование пользователя #<?= (int) $user->id ?></h1>
        <a class="admin-back" href="<?= Html::encode(Url::to(['/admin/users'])) ?>">← Назад</a>
    </div>

    <div class="admin-form-wrap card-like">
        <form method="post" action="<?= Html::encode(Url::to(['/admin/user-update', 'id' => (int) $user->id])) ?>">
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Html::encode(Yii::$app->request->csrfToken) ?>">

            <div class="admin-form-row">
                <label class="admin-label">Email</label>
                <div class="admin-static"><?= Html::encode($user->email) ?></div>
            </div>

            <div class="admin-form-row">
                <label class="admin-label">Имя</label>
                <div class="admin-static"><?= Html::encode($user->getDisplayName()) ?></div>
            </div>

            <div class="admin-form-row">
                <label class="admin-label" for="user-role">Роль</label>
                <select name="role" id="user-role" class="admin-select">
                    <option value="<?= User::ROLE_DEFAULT ?>" <?= $user->role === User::ROLE_DEFAULT ? 'selected' : '' ?>>Пользователь</option>
                    <option value="<?= User::ROLE_SELLER ?>" <?= $user->role === User::ROLE_SELLER ? 'selected' : '' ?>>Продавец</option>
                    <option value="<?= User::ROLE_ADMIN ?>" <?= $user->role === User::ROLE_ADMIN ? 'selected' : '' ?>>Администратор</option>
                </select>
            </div>

            <div class="admin-form-row">
                <label class="admin-label" for="user-status">Статус аккаунта</label>
                <select name="status" id="user-status" class="admin-select">
                    <option value="<?= User::STATUS_ACTIVE ?>" <?= ($user->status ?? User::STATUS_ACTIVE) === User::STATUS_ACTIVE ? 'selected' : '' ?>>Активен</option>
                    <option value="<?= User::STATUS_BLOCKED ?>" <?= ($user->status ?? '') === User::STATUS_BLOCKED ? 'selected' : '' ?>>Заблокирован</option>
                </select>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn--primary">Сохранить</button>
                <a href="<?= Html::encode(Url::to(['/admin/users'])) ?>" class="admin-btn">Отмена</a>
            </div>
        </form>
    </div>
</div>
