<?php
/** @var yii\web\View $this */
/** @var app\models\User[] $users */
/** @var yii\data\Pagination $pages */
/** @var string $search */

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Пользователи';
?>
<div class="admin-wrap">
    <div class="admin-head">
        <h1 class="admin-title">Пользователи</h1>
        <a class="admin-back" href="<?= Html::encode(Url::to(['/admin'])) ?>">← Назад</a>
    </div>

    <form class="admin-search-form" action="<?= Html::encode(Url::to(['/admin/users'])) ?>" method="get">
        <input
            type="text"
            name="search"
            class="admin-search-input"
            placeholder="Поиск по email, имени..."
            value="<?= Html::encode($search) ?>"
        >
        <button type="submit" class="admin-search-btn">Найти</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Имя</th>
                    <th>Роль</th>
                    <th>Статус</th>
                    <th>Дата регистрации</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="<?= $user->isBlocked() ? 'admin-row--blocked' : '' ?>">
                        <td><?= (int) $user->id ?></td>
                        <td><?= Html::encode($user->email) ?></td>
                        <td><?= Html::encode($user->getDisplayName()) ?></td>
                        <td>
                            <span class="admin-badge admin-badge--<?= Html::encode($user->role ?? 'user') ?>">
                                <?= Html::encode($user->role ?? 'user') ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user->isBlocked()): ?>
                                <span class="admin-badge admin-badge--danger">Заблокирован</span>
                            <?php else: ?>
                                <span class="admin-badge admin-badge--ok">Активен</span>
                            <?php endif; ?>
                        </td>
                        <td><?= Html::encode($user->created_at ?? '—') ?></td>
                        <td class="admin-actions">
                            <a href="<?= Html::encode(Url::to(['/admin/user-update', 'id' => (int) $user->id])) ?>" class="admin-btn admin-btn--edit">Редактировать</a>
                            <?php if ((int) $user->id !== (int) Yii::$app->user->id): ?>
                                <a href="<?= Html::encode(Url::to(['/admin/user-delete', 'id' => (int) $user->id])) ?>" class="admin-btn admin-btn--delete" data-confirm="Удалить пользователя?">Удалить</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?= LinkPager::widget([
        'pagination' => $pages,
        'options' => ['class' => 'pagination admin-pagination'],
        'linkOptions' => ['class' => 'page-link'],
        'activePageCssClass' => 'active',
        'disabledPageCssClass' => 'disabled',
    ]) ?>
</div>
