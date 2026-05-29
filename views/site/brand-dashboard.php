<?php

/** @var yii\web\View $this */
/** @var array<string, mixed> $brand */
/** @var array<int, array<string, mixed>> $dashboardProducts */
/** @var string $listTab */
/** @var int $totalProducts */
/** @var int $activeProductsCount */
/** @var string $conceptText */
/** @var string $historyText */
/** @var string $yearFounded */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\helpers\Url;

$name = (string) ($brand['name'] ?? '');
$city = trim((string) ($brand['city'] ?? ''));
$logo = trim((string) ($brand['logo'] ?? ''));

$this->title = 'Панель управления — ' . $name;
?>
<div class="seller-dash">
    <?= Alert::widget() ?>

    <header class="seller-dash-header card-like">
        <div class="seller-dash-header-main">
            <?php if ($logo !== ''): ?>
                <div class="seller-dash-logo-wrap">
                    <img src="<?= Html::encode(Url::to('@web/' . ltrim($logo, '/'))) ?>" alt="" class="seller-dash-logo-img" width="72" height="72" loading="lazy" decoding="async">
                </div>
            <?php else: ?>
                <div class="seller-dash-logo-fallback" aria-hidden="true"></div>
            <?php endif; ?>
            <div class="seller-dash-header-text">
                <h1 class="seller-dash-brand-name"><?= Html::encode($name) ?></h1>
                <p class="seller-dash-sub">Панель управления</p>
            </div>
        </div>
        <a class="seller-dash-btn-add" href="<?= Html::encode(Url::to(['seller/add-product'])) ?>">+ Добавить товар</a>
    </header>

    <section class="seller-dash-stats" aria-label="Показатели">
        <div class="seller-dash-stat card-like">
            <div class="seller-dash-stat-value">0</div>
            <div class="seller-dash-stat-label">Выручка</div>
        </div>
        <div class="seller-dash-stat card-like">
            <div class="seller-dash-stat-value">0</div>
            <div class="seller-dash-stat-label">Заказы</div>
        </div>
        <div class="seller-dash-stat card-like">
            <div class="seller-dash-stat-value">0</div>
            <div class="seller-dash-stat-label">Просмотры</div>
        </div>
        <div class="seller-dash-stat card-like">
            <div class="seller-dash-stat-value"><?= (int) $activeProductsCount ?></div>
            <div class="seller-dash-stat-label">Активные</div>
        </div>
    </section>

    <section class="seller-dash-info card-like">
        <div class="seller-dash-info-head">
            <h2 class="seller-dash-section-title">Информация о бренде</h2>
            <a class="seller-dash-link-edit" href="<?= Html::encode(Url::to(['seller/edit-brand'])) ?>">Редактировать</a>
        </div>
        <div class="seller-dash-info-grid">
            <div class="seller-dash-info-col">
                <dl class="seller-dash-dl">
                    <dt>Название бренда</dt>
                    <dd><?= Html::encode($name) ?></dd>
                    <dt>Город</dt>
                    <dd><?= $city !== '' ? Html::encode($city) : '—' ?></dd>
                    <dt>Всего товара</dt>
                    <dd><?= (int) $totalProducts ?></dd>
                </dl>
            </div>
            <div class="seller-dash-info-col">
                <dl class="seller-dash-dl">
                    <dt>Год основания</dt>
                    <dd><?= $yearFounded !== '' ? Html::encode($yearFounded) : '—' ?></dd>
                    <dt>Коллекций</dt>
                    <dd>1</dd>
                </dl>
            </div>
            <div class="seller-dash-info-col seller-dash-info-col--wide">
                <div class="seller-dash-info-block">
                    <div class="seller-dash-info-block-title">Концепция бренда</div>
                    <p class="seller-dash-info-text"><?= nl2br(Html::encode($conceptText !== '' ? $conceptText : '—')) ?></p>
                </div>
                <?php if ($historyText !== '' && $historyText !== $conceptText): ?>
                    <div class="seller-dash-info-block">
                        <div class="seller-dash-info-block-title">История и описание</div>
                        <p class="seller-dash-info-text"><?= nl2br(Html::encode($historyText)) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="seller-dash-listings card-like">
        <div class="seller-dash-listings-head">
            <h2 class="seller-dash-section-title">Активные объявления</h2>
            <div class="seller-dash-tabs" role="tablist">
                <a
                    class="seller-dash-tab <?= $listTab === 'active' ? 'is-active' : '' ?>"
                    href="<?= Html::encode(Url::to(['seller/brand-dashboard', 'list' => 'active'])) ?>"
                >Все</a>
                <a
                    class="seller-dash-tab <?= $listTab === 'archive' ? 'is-active' : '' ?>"
                    href="<?= Html::encode(Url::to(['seller/brand-dashboard', 'list' => 'archive'])) ?>"
                >Архив</a>
            </div>
        </div>

        <?php if (empty($dashboardProducts)): ?>
            <p class="seller-dash-empty">В этом разделе пока нет товаров.</p>
        <?php else: ?>
            <div class="seller-dash-product-grid">
                <?php foreach ($dashboardProducts as $p): ?>
                    <article class="seller-dash-pcard">
                        <div class="seller-dash-pcard-img-wrap">
                            <?php if (!empty($p['image'])): ?>
                                <img
                                    src="<?= Html::encode(Url::to('@web/' . ltrim((string) $p['image'], '/'))) ?>"
                                    alt=""
                                    class="seller-dash-pcard-img"
                                    loading="lazy"
                                    decoding="async"
                                >
                            <?php else: ?>
                                <div class="seller-dash-pcard-img seller-dash-pcard-img--empty" aria-hidden="true"></div>
                            <?php endif; ?>
                        </div>
                        <div class="seller-dash-pcard-body">
                            <div class="seller-dash-pcard-title"><?= Html::encode($p['name'] ?? '') ?></div>
                            <div class="seller-dash-pcard-id">ID: <?= (int) ($p['id'] ?? 0) ?></div>
                            <a class="seller-dash-pcard-edit" href="<?= Html::encode(Url::to(['seller/edit-product', 'id' => (int) ($p['id'] ?? 0)])) ?>">Редактировать</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
