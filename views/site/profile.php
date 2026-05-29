<?php

/** @var yii\web\View $this */
/** @var array $recommendedProducts */
/** @var string $tab */
/** @var array $orders */
/** @var array $favoriteProducts */
/** @var \app\models\ProfileSettingsForm $settingsForm */
/** @var int $favoriteCount */
/** @var int $activeOrdersCount */
/** @var int[] $favoriteProductIds */
/** @var array<string, mixed>|null $sellerBrand */

use app\models\User;
use app\widgets\Alert;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Профиль';

$tab = $tab ?? 'overview';
$orders = $orders ?? [];
$favoriteProducts = $favoriteProducts ?? [];
$favoriteCount = (int) ($favoriteCount ?? 0);
$activeOrdersCount = (int) ($activeOrdersCount ?? 0);
$favoriteProductIds = array_map('intval', $favoriteProductIds ?? []);

$orderStatusLabels = [
    'new' => 'Новый',
    'paid' => 'Оплачен',
    'processing' => 'В обработке',
    'shipped' => 'Отправлен',
    'delivered' => 'Доставлен',
    'cancelled' => 'Отменён',
];

/** @var \app\models\User|null $identity */
$identity = Yii::$app->user->identity;
$displayName = '';
if ($identity !== null) {
    $fn = $identity->hasAttribute('first_name') ? trim((string) $identity->getAttribute('first_name')) : '';
    $ln = $identity->hasAttribute('last_name') ? trim((string) $identity->getAttribute('last_name')) : '';
    $displayName = trim($fn . ' ' . $ln);
    if ($displayName === '') {
        $displayName = (string) $identity->getAttribute('email');
    }
}
$recommendedProducts = $recommendedProducts ?? [];
$sellerBrand = $sellerBrand ?? null;

$avatarPath = $identity !== null ? $identity->getAvatarPath() : User::DEFAULT_AVATAR;
$avatarUrl = Url::to('@web/' . ltrim($avatarPath, '/'));

$navClass = static function (string $name) use ($tab): string {
    return $name === $tab ? 'profile-nav-link is-active' : 'profile-nav-link';
};
?>
<div class="profile-page">
    <?= Alert::widget() ?>

    <div class="profile-top card-like">
        <div class="profile-top-left">
            <div class="profile-avatar">
                <img
                    src="<?= Html::encode($avatarUrl) ?>"
                    alt=""
                    class="profile-avatar-img"
                    width="56"
                    height="56"
                    loading="lazy"
                    decoding="async"
                >
            </div>
            <span class="profile-name"><?= Html::encode($displayName) ?></span>
        </div>
        <div class="profile-top-right">
            <?php if ($sellerBrand !== null): ?>
                <a href="<?= Html::encode(Url::to(['seller/brand-dashboard'])) ?>" class="profile-btn-outline">Управление брендом</a>
            <?php else: ?>
                <a href="<?= Html::encode(Url::to(['seller/register-brand'])) ?>" class="profile-btn-outline">Стать продавцом</a>
            <?php endif; ?>
            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'profile-logout-form']) ?>
            <button type="submit" class="profile-icon-btn" aria-label="Выйти" title="Выйти">
                <?= Html::img(Url::to('@web/images/Exit.svg'), ['alt' => '', 'width' => 22, 'height' => 22, 'decoding' => 'async']) ?>
            </button>
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="profile-layout">
        <aside class="profile-sidebar card-like">
            <nav class="profile-nav" aria-label="Разделы профиля">
                <a class="<?= Html::encode($navClass('overview')) ?>" href="<?= Html::encode(Url::to(['/site/profile'])) ?>">Обзор</a>
                <a class="<?= Html::encode($navClass('orders')) ?>" href="<?= Html::encode(Url::to(['/site/profile', 'tab' => 'orders'])) ?>">Заказы</a>
                <a class="<?= Html::encode($navClass('favorites')) ?>" href="<?= Html::encode(Url::to(['/site/profile', 'tab' => 'favorites'])) ?>">Избранное</a>
                <a class="<?= Html::encode($navClass('settings')) ?>" href="<?= Html::encode(Url::to(['/site/profile', 'tab' => 'settings'])) ?>">Настройки</a>
            </nav>
        </aside>

        <section class="profile-main card-like">
            <?php if ($tab === 'overview'): ?>
                <h2 class="profile-section-title">Последняя активность</h2>
                <div class="profile-stats">
                    <div class="profile-stat card-like">
                        <div class="profile-stat-value"><?= (int) $activeOrdersCount ?></div>
                        <div class="profile-stat-label">Активных заказов</div>
                    </div>
                    <div class="profile-stat card-like">
                        <div class="profile-stat-value"><?= (int) $favoriteCount ?></div>
                        <div class="profile-stat-label">В избранном</div>
                    </div>
                    <div class="profile-stat card-like">
                        <div class="profile-stat-value">0</div>
                        <div class="profile-stat-label">Продаж</div>
                    </div>
                </div>

                <h2 class="profile-section-title profile-section-title-spaced">Рекомендации</h2>
                <div class="profile-reco-row">
                    <?php foreach ($recommendedProducts as $product): ?>
                        <article class="profile-reco-card card-like profile-reco-card--wrap">
                            <a href="<?= Html::encode(Url::to(['/site/product', 'id' => (int) $product['id']])) ?>" class="profile-reco-link">
                                <?php if (!empty($product['image'])): ?>
                                    <img
                                        src="<?= Html::encode(Url::to('@web/' . ltrim((string) $product['image'], '/'))) ?>"
                                        alt="<?= Html::encode($product['name']) ?>"
                                        width="230"
                                        height="230"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                <?php else: ?>
                                    <div class="profile-reco-fallback" aria-hidden="true"></div>
                                <?php endif; ?>
                            </a>
                            <?= $this->render('_favorite_btn', [
                                'productId' => (int) $product['id'],
                                'isFavorite' => in_array((int) $product['id'], $favoriteProductIds, true),
                                'extraClass' => 'fav-btn--sm fav-btn--on-card',
                            ]) ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($tab === 'orders'): ?>
                <h2 class="profile-panel-title">Заказы</h2>
                <?php if (empty($orders)): ?>
                    <div class="profile-empty-block">
                        <p class="profile-empty-title">История заказов пуста</p>
                        <p class="profile-empty-sub">Вы пока ничего не приобрели</p>
                        <a class="profile-btn-catalog" href="<?= Html::encode(Url::to(['/site/catalog'])) ?>">Перейти в каталог</a>
                    </div>
                <?php else: ?>
                    <div class="profile-table-wrap">
                        <table class="profile-table">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Дата</th>
                                    <th>Статус</th>
                                    <th class="profile-table-num">Сумма</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <?php
                                    $st = (string) ($order['status'] ?? 'new');
                                    $label = $orderStatusLabels[$st] ?? $st;
                                    ?>
                                    <tr>
                                        <td>#<?= (int) ($order['id'] ?? 0) ?></td>
                                        <td><?php
                                            $created = $order['created_at'] ?? null;
                                            echo $created !== null && $created !== ''
                                                ? Html::encode(Yii::$app->formatter->asDatetime($created, 'php:d.m.Y H:i'))
                                                : '—';
                                        ?></td>
                                        <td><?= Html::encode($label) ?></td>
                                        <td class="profile-table-num"><?= Html::encode(number_format((float) ($order['total'] ?? 0), 2, ',', ' ')) ?> ₽</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php elseif ($tab === 'favorites'): ?>
                <h2 class="profile-panel-title">Избранное</h2>
                <?php if (empty($favoriteProducts)): ?>
                    <div class="profile-empty-block profile-empty-block--fav">
                        <p class="profile-empty-title">Список избранного пуст</p>
                        <p class="profile-empty-sub">Добавляйте товары кнопкой «♥» в каталоге или на странице товара</p>
                        <a class="profile-btn-catalog" href="<?= Html::encode(Url::to(['/site/catalog'])) ?>">Перейти в каталог</a>
                    </div>
                <?php else: ?>
                    <div class="profile-fav-grid">
                        <?php foreach ($favoriteProducts as $product): ?>
                            <article class="profile-fav-card">
                                <div class="profile-fav-media">
                                    <a href="<?= Html::encode(Url::to(['/site/product', 'id' => (int) $product['id']])) ?>" class="profile-fav-img-link">
                                        <?php if (!empty($product['image'])): ?>
                                            <img
                                                src="<?= Html::encode(Url::to('@web/' . ltrim((string) $product['image'], '/'))) ?>"
                                                alt="<?= Html::encode($product['name'] ?? '') ?>"
                                                loading="lazy"
                                                decoding="async"
                                            >
                                        <?php else: ?>
                                            <div class="profile-fav-img-fallback" aria-hidden="true"></div>
                                        <?php endif; ?>
                                    </a>
                                    <?= $this->render('_favorite_btn', [
                                        'productId' => (int) $product['id'],
                                        'isFavorite' => true,
                                        'extraClass' => 'fav-btn--sm fav-btn--on-card',
                                    ]) ?>
                                </div>
                                <div class="profile-fav-meta">
                                    <a class="profile-fav-name" href="<?= Html::encode(Url::to(['/site/product', 'id' => (int) $product['id']])) ?>"><?= Html::encode($product['name'] ?? '') ?></a>
                                    <div class="profile-fav-price"><?= Html::encode(number_format((float) ($product['price'] ?? 0), 0, '', ' ')) ?>₽</div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php elseif ($tab === 'settings'): ?>
                <h2 class="profile-panel-title profile-panel-title--settings">Настройки аккаунта</h2>

                <?php $form = ActiveForm::begin([
                    'id' => 'profile-settings-form',
                    'action' => Url::to(['/site/profile', 'tab' => 'settings']),
                    'options' => [
                        'class' => 'profile-settings-form',
                        'enctype' => 'multipart/form-data',
                    ],
                    'fieldConfig' => [
                        'template' => "{label}\n{input}\n{error}",
                        'labelOptions' => ['class' => 'profile-settings-label'],
                        'inputOptions' => ['class' => 'profile-settings-input'],
                        'errorOptions' => ['class' => 'profile-settings-error'],
                    ],
                ]); ?>

                <div class="profile-settings-section">
                    <h3 class="profile-settings-section-title">Аватар</h3>
                    <div class="profile-avatar-upload">
                        <img
                            src="<?= Html::encode(Url::to('@web/' . ltrim($settingsForm->user->getAvatarPath(), '/'))) ?>"
                            alt=""
                            class="profile-avatar-upload-preview"
                            width="80"
                            height="80"
                            id="profile-avatar-preview"
                            loading="lazy"
                            decoding="async"
                        >
                        <?= $form->field($settingsForm, 'avatarFile', [
                            'options' => ['class' => 'profile-settings-field profile-settings-field--full profile-avatar-file-field'],
                        ])->fileInput([
                            'class' => 'profile-settings-input profile-settings-input--file',
                            'accept' => 'image/jpeg,image/png,image/gif,image/webp',
                            'id' => 'profile-avatar-file',
                        ]) ?>
                        <p class="profile-avatar-hint">JPG, PNG, GIF или WebP, до 5 МБ</p>
                    </div>
                </div>

                <div class="profile-settings-section">
                    <h3 class="profile-settings-section-title">Личные данные</h3>
                    <div class="profile-settings-row">
                        <?= $form->field($settingsForm, 'first_name', [
                            'options' => ['class' => 'profile-settings-field profile-settings-field--half'],
                        ])->textInput(['autocomplete' => 'given-name']) ?>
                        <?= $form->field($settingsForm, 'last_name', [
                            'options' => ['class' => 'profile-settings-field profile-settings-field--half'],
                        ])->textInput(['autocomplete' => 'family-name']) ?>
                    </div>
                    <?= $form->field($settingsForm, 'email', [
                        'options' => ['class' => 'profile-settings-field profile-settings-field--full'],
                    ])->textInput([
                        'readonly' => true,
                        'class' => 'profile-settings-input profile-settings-input--readonly',
                        'autocomplete' => 'email',
                    ]) ?>
                </div>

                <div class="profile-settings-section">
                    <h3 class="profile-settings-section-title">Безопасность</h3>
                    <?= $form->field($settingsForm, 'new_password', [
                        'options' => ['class' => 'profile-settings-field profile-settings-field--full'],
                    ])->passwordInput([
                        'placeholder' => 'Новый пароль',
                        'autocomplete' => 'new-password',
                    ]) ?>
                </div>

                <div class="profile-settings-section">
                    <h3 class="profile-settings-section-title">Уведомление</h3>
                    <div class="profile-check-list">
                        <?= $form->field($settingsForm, 'notify_news', [
                            'options' => ['class' => 'profile-check-field'],
                            'template' => "{input}\n{error}",
                        ])->checkbox([
                            'class' => 'profile-check-input form-check-input',
                            'label' => '<span class="profile-check-text">' . Html::encode($settingsForm->getAttributeLabel('notify_news')) . '</span>',
                            'encode' => false,
                            'uncheck' => '0',
                            'value' => '1',
                        ], false) ?>
                        <?= $form->field($settingsForm, 'notify_orders', [
                            'options' => ['class' => 'profile-check-field'],
                            'template' => "{input}\n{error}",
                        ])->checkbox([
                            'class' => 'profile-check-input form-check-input',
                            'label' => '<span class="profile-check-text">' . Html::encode($settingsForm->getAttributeLabel('notify_orders')) . '</span>',
                            'encode' => false,
                            'uncheck' => '0',
                            'value' => '1',
                        ], false) ?>
                    </div>
                </div>

                <div class="profile-settings-actions">
                    <?= Html::submitButton('Сохранить изменения', ['class' => 'profile-settings-submit']) ?>
                </div>
                <?php ActiveForm::end(); ?>

                <?php
                $this->registerJs(<<<'JS'
(function () {
    var input = document.getElementById('profile-avatar-file');
    var preview = document.getElementById('profile-avatar-preview');
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
            <?php endif; ?>
        </section>
    </div>
</div>
