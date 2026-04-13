<?php

/** @var yii\web\View $this */
/** @var array $recommendedProducts */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Профиль';

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
?>
<div class="profile-page">
    <div class="profile-top card-like">
        <div class="profile-top-left">
            <div class="profile-avatar" aria-hidden="true"></div>
            <span class="profile-name"><?= Html::encode($displayName) ?></span>
        </div>
        <div class="profile-top-right">
            <a href="#" class="profile-btn-outline">Стать продавцом</a>
            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'profile-logout-form']) ?>
            <button type="submit" class="profile-icon-btn" aria-label="Выйти" title="Выйти">
                <?= Html::img(Url::to('@web/images/Exit.svg'), ['alt' => '', 'width' => 22, 'height' => 22, 'decoding' => 'async']) ?>
            </button>
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="profile-layout">
        <aside class="profile-sidebar card-like">
            <nav class="profile-nav">
                <a class="profile-nav-link is-active" href="<?= Html::encode(Url::to(['/site/profile'])) ?>">Обзор</a>
                <a class="profile-nav-link" href="#">Заказы</a>
                <a class="profile-nav-link" href="#">Избранное</a>
                <a class="profile-nav-link" href="#">Настройки</a>
            </nav>
        </aside>

        <section class="profile-main card-like">
            <h2 class="profile-section-title">Последняя активность</h2>
            <div class="profile-stats">
                <div class="profile-stat card-like">
                    <div class="profile-stat-value">0</div>
                    <div class="profile-stat-label">Активных заказов</div>
                </div>
                <div class="profile-stat card-like">
                    <div class="profile-stat-value">0</div>
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
                    <article class="profile-reco-card card-like">
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
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
