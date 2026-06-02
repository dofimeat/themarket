<?php
/** @var yii\web\View $this */
/** @var array $product */
/** @var array $images */
/** @var array $sizes */
/** @var array $features */
/** @var array $recommended */
/** @var bool $isFavorite */
/** @var int[] $favoriteProductIds */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = (string) ($product['name'] ?? 'Товар');
$images = $images ?? [];
$sizes = $sizes ?? [];
$features = $features ?? [];
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
    <?= Alert::widget() ?>
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
                    <select class="form-select product-select" aria-label="Размер" name="size" id="product-size-select">
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
                    <button class="btn product-add" type="button" id="product-add-to-cart"
                        data-product-id="<?= (int) ($product['id'] ?? 0) ?>"
                        data-csrf="<?= Yii::$app->request->csrfToken ?>">
                        Добавить в корзину
                    </button>
                </div>

                <?php
                $brandId = (int) ($product['brand_id'] ?? 0);
                $brandLogo = trim((string) ($product['brand_logo'] ?? ''));
                $brandUrl = $brandId > 0 ? Url::to(['brand', 'id' => $brandId]) : '#';
                ?>
                <?php if ($brandId > 0): ?>
                    <a href="<?= Html::encode($brandUrl) ?>" class="product-brand-box-centered" style="text-decoration:none;">
                        <?php if ($brandLogo !== '' && $brandLogo !== 'images/defolt-avatar.png'): ?>
                            <img src="<?= Html::encode(Url::to('@web/' . ltrim($brandLogo, '/'))) ?>" alt="<?= Html::encode($brandName) ?>" class="product-brand-logo">
                        <?php endif; ?>
                        <span><?= Html::encode($brandName !== '' ? $brandName : 'Бренд') ?></span>
                    </a>
                <?php else: ?>
                    <div class="product-brand-box-centered">
                        <?= Html::encode($brandName !== '' ? $brandName : 'RetroMakers') ?>
                    </div>
                <?php endif; ?>

                <div class="product-section">
                    <div class="product-section-title">Описание:</div>
                    <div class="product-section-text">
                        <?= nl2br(Html::encode((string) ($product['description'] ?? ''))) ?>
                    </div>
                </div>

                <div class="product-section">
                    <div class="product-section-title">Характеристики:</div>
                    <?php if (!empty($features)): ?>
                        <ul class="product-specs-list">
                            <?php foreach ($features as $feat): ?>
                                <?php
                                $featName = trim((string) ($feat['name'] ?? ''));
                                $featValue = trim((string) ($feat['value'] ?? ''));
                                if ($featName === '' && $featValue === '') {
                                    continue;
                                }
                                ?>
                                <li>
                                    <?= Html::encode($featName) ?>
                                    <?php if ($featName !== '' && $featValue !== ''): ?>:
                                    <?php endif; ?>
                                    <?= Html::encode($featValue) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="product-section-text" style="color: #999;">Характеристики не указаны</div>
                    <?php endif; ?>
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
<script>
(function(){
    var btn = document.getElementById('product-add-to-cart');
    var sizeSelect = document.getElementById('product-size-select');
    if (!btn) return;

    btn.addEventListener('click', function(){
        var productId = btn.getAttribute('data-product-id');
        var csrf = btn.getAttribute('data-csrf');
        var size = sizeSelect ? sizeSelect.value : '';

        btn.disabled = true;
        btn.textContent = 'Добавление…';

        var fd = new FormData();
        fd.append('product_id', productId);
        fd.append('size', size);
        fd.append('quantity', 1);
        fd.append('_csrf', csrf);

        fetch('<?= Url::to(['/cart/add']) ?>', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.success) {
                    btn.textContent = '✓ В корзине';
                    showFlashAlert('success', 'Товар «' + document.querySelector('.product-name').textContent + '» добавлен в корзину');
                    if (window.updateCartBadge) window.updateCartBadge();
                    setTimeout(function(){
                        btn.disabled = false;
                        btn.textContent = 'Добавить в корзину';
                    }, 2000);
                } else {
                    btn.disabled = false;
                    btn.textContent = 'Добавить в корзину';
                    showFlashAlert('danger', d.error || 'Ошибка');
                }
            })
            .catch(function(){
                btn.disabled = false;
                btn.textContent = 'Добавить в корзину';
                showFlashAlert('danger', 'Ошибка соединения');
            });
    });

    function showFlashAlert(type, msg) {
        var wrap = document.querySelector('.home-wrap.product-view-wrap');
        var existing = wrap.querySelector('.alert-flash');
        if (existing) existing.remove();

        var alert = document.createElement('div');
        alert.className = 'alert alert-' + type + ' alert-dismissible fade show alert-flash';
        alert.setAttribute('role', 'alert');
        alert.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';

        var firstChild = wrap.querySelector('section, .product-centered');
        wrap.insertBefore(alert, firstChild);

        setTimeout(function(){
            if (alert.parentNode) {
                alert.classList.remove('show');
                setTimeout(function(){ alert.remove(); }, 300);
            }
        }, 4000);
    }
})();
</script>
