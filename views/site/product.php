<?php
/** @var yii\web\View $this */
/** @var array $product */
/** @var array $images */
/** @var array $sizes */
/** @var array $recommended */
/** @var bool $isFavorite */
/** @var int[] $favoriteProductIds */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = (string) ($product['name'] ?? 'Товар');
$images = $images ?? [];
$sizes = $sizes ?? [];
$recommended = $recommended ?? [];
$isFavorite = (bool) ($isFavorite ?? false);
$favoriteProductIds = array_map('intval', $favoriteProductIds ?? []);

$brandName = trim((string) ($product['brand_name'] ?? ''));
$hasMultipleImages = count($images) > 1;

if ($hasMultipleImages) {
    $this->registerJs(<<<JS
(function () {
    var el = document.getElementById('productCarousel');
    if (!el || !window.bootstrap || !window.bootstrap.Carousel) {
        return;
    }

    var carousel = bootstrap.Carousel.getOrCreateInstance(el, {
        interval: false,
        ride: false,
        touch: true,
        wrap: true
    });

    var startX = 0;
    var isDragging = false;
    var dragThreshold = 50;

    el.addEventListener('dragstart', function (e) { e.preventDefault(); });

    el.addEventListener('pointerdown', function (e) {
        if (e.pointerType !== 'mouse' || e.button !== 0) return;
        if (e.target.closest('.carousel-control-prev, .carousel-control-next, .carousel-indicators, .product-media-favorite')) return;
        isDragging = true;
        startX = e.clientX;
        el.setPointerCapture(e.pointerId);
    });

    el.addEventListener('pointerup', function (e) {
        if (!isDragging) return;
        isDragging = false;
        if (e.target.closest('.carousel-control-prev, .carousel-control-next, .carousel-indicators, .product-media-favorite')) {
            el.releasePointerCapture(e.pointerId);
            return;
        }
        var distance = e.clientX - startX;
        el.releasePointerCapture(e.pointerId);
        if (Math.abs(distance) < dragThreshold) return;
        if (distance > 0) carousel.prev();
        else carousel.next();
    });

    el.addEventListener('pointercancel', function () { isDragging = false; });
})();
JS
    );
}
?>

<div class="home-wrap product-view-wrap">
    <section class="product-centered">
        <div class="product-main">
            <div class="product-media">
                <div id="productCarousel" class="carousel slide product-carousel" data-bs-touch="true">
                    <div class="product-carousel-stage">
                        <div class="carousel-inner">
                            <?php foreach ($images as $idx => $src): ?>
                                <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                                    <img
                                        class="product-photo d-block w-100"
                                        src="<?= Html::encode(Url::to('@web/' . ltrim((string) $src, '/'))) ?>"
                                        alt="<?= Html::encode((string) ($product['name'] ?? '')) ?>"
                                        loading="<?= $idx === 0 ? 'eager' : 'lazy' ?>"
                                        draggable="false"
                                    >
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($hasMultipleImages): ?>
                            <button class="carousel-control-prev product-carousel-control" type="button" data-bs-target="#productCarousel" data-bs-slide="prev" aria-label="Предыдущее фото">
                                <span class="product-carousel-arrow product-carousel-arrow--prev" aria-hidden="true"></span>
                            </button>
                            <button class="carousel-control-next product-carousel-control" type="button" data-bs-target="#productCarousel" data-bs-slide="next" aria-label="Следующее фото">
                                <span class="product-carousel-arrow product-carousel-arrow--next" aria-hidden="true"></span>
                            </button>
                        <?php endif; ?>

                        <div class="product-media-favorite">
                            <?= $this->render('_favorite_btn', [
                                'productId' => (int) ($product['id'] ?? 0),
                                'isFavorite' => $isFavorite,
                                'extraClass' => 'fav-btn--sm fav-btn--on-gallery',
                            ]) ?>
                        </div>
                    </div>

                    <?php if ($hasMultipleImages): ?>
                        <div class="carousel-indicators product-indicators">
                            <?php foreach ($images as $idx => $_): ?>
                                <button
                                    type="button"
                                    data-bs-target="#productCarousel"
                                    data-bs-slide-to="<?= (int) $idx ?>"
                                    class="<?= $idx === 0 ? 'active' : '' ?>"
                                    <?= $idx === 0 ? 'aria-current="true"' : '' ?>
                                    aria-label="Фото <?= (int) $idx + 1 ?>"
                                ></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="product-info-wrap">
                <h1 class="product-name"><?= Html::encode((string) ($product['name'] ?? '')) ?></h1>
                <div class="product-price"><?= number_format((float) ($product['price'] ?? 0), 0, '', ' ') ?> ₽</div>

                <div class="product-field">
                    <div class="product-label">Размер:</div>
                    <select class="form-select product-select" aria-label="Размер" name="size">
                        <?php if (empty($sizes)): ?>
                            <option value="" selected>—</option>
                        <?php else: ?>
                            <?php foreach ($sizes as $sizeRow): ?>
                                <?php
                                $sizeLabel = trim((string) ($sizeRow['size'] ?? ''));
                                if ($sizeLabel === '') {
                                    continue;
                                }
                                $qty = (int) ($sizeRow['quantity'] ?? 0);
                                $inStock = $qty > 0;
                                ?>
                                <option value="<?= Html::encode($sizeLabel) ?>" <?= $inStock ? '' : 'disabled' ?>>
                                    <?= Html::encode($sizeLabel) ?><?= $inStock ? '' : ' (нет в наличии)' ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="product-actions-stack">
                    <button class="btn product-add" type="button">Добавить в корзину</button>
                </div>

                <div class="product-brand-box-centered">
                    <?= Html::encode($brandName !== '' ? $brandName : 'RetroMakers') ?>
                </div>

                <div class="product-section">
                    <div class="product-section-title">Описание:</div>
                    <div class="product-section-text">
                        <?= nl2br(Html::encode((string) ($product['description'] ?? ''))) ?>
                    </div>
                </div>

                <div class="product-section">
                    <div class="product-section-title">Характеристики:</div>
                    <ul class="product-specs-list">
                        <li>Материал: 100% Хлопок / Техническая ткань</li>
                        <li>Цвет: Белый / Антрацит</li>
                        <li>Ручная работа</li>
                        <li>Пол: унисекс</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if (!empty($recommended)): ?>
            <div class="product-reco" style="width: 100%; margin-top: 120px; border-top: 1px solid #F0F0F0; padding-top: 64px;">
                <div class="product-reco-title" style="font-size: 14px; color: #999; margin-bottom: 32px;">Вам может понравиться</div>
                <div class="product-grid">
                    <?php foreach ($recommended as $rec): ?>
                        <div class="product-card-wrap product-card-wrap--reco">
                            <a class="product-card" href="<?= Html::encode(Url::to(['product', 'id' => (int) $rec['id']])) ?>" style="text-decoration: none; color: inherit;">
                                <?php if (!empty($rec['image'])): ?>
                                    <img
                                        src="<?= Html::encode(Url::to('@web/' . ltrim($rec['image'], '/'))) ?>"
                                        class="product-image"
                                        alt="<?= Html::encode($rec['name'] ?? '') ?>"
                                        loading="lazy"
                                        draggable="false"
                                    >
                                <?php else: ?>
                                    <div class="product-image" style="background: #f2f2f2;"></div>
                                <?php endif; ?>
                                <h3><?= Html::encode($rec['name'] ?? '') ?></h3>
                                <p><?= number_format((float) ($rec['price'] ?? 0), 0, '', ' ') ?> ₽</p>
                            </a>
                            <?= $this->render('_favorite_btn', [
                                'productId' => (int) $rec['id'],
                                'isFavorite' => in_array((int) $rec['id'], $favoriteProductIds, true),
                                'extraClass' => 'fav-btn--sm fav-btn--on-card',
                            ]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>
