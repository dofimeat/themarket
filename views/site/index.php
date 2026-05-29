<?php
/** @var yii\web\View $this */
/** @var array $products */
/** @var int[] $favoriteProductIds */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'TheMarket';
$products = $products ?? [];
$favoriteProductIds = array_map('intval', $favoriteProductIds ?? []);
$productSlides = array_chunk($products, 4);
if (empty($productSlides)) {
    $productSlides = [[]];
}
?>

<section class="hero">
    <div id="mainHeroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" data-bs-touch="true" data-bs-pause="false">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#mainHeroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#mainHeroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#mainHeroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="images/slader1.jpg" class="hero-image" alt="Главный баннер" draggable="false">
            </div>
            <div class="carousel-item">
                <img src="images/slader2.jpg" class="hero-image" alt="Главный баннер 2" draggable="false">
            </div>
            <div class="carousel-item">
                <img src="images/slader3.jpg" class="hero-image" alt="Главный баннер 3" draggable="false">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mainHeroCarousel" data-bs-slide="prev" aria-label="Previous">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainHeroCarousel" data-bs-slide="next" aria-label="Next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    </div>
</section>

<section class="block products">
    <div class="block-head">
        <h2>Новинки/</h2>
        <a href="<?= Url::to(['/site/catalog']) ?>" class="pill">Все изделия</a>
    </div>
    <div id="productsCarousel" class="carousel slide products-carousel" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-indicators products-indicators">
            <?php foreach ($productSlides as $slideIndex => $slide): ?>
                <button
                    type="button"
                    data-bs-target="#productsCarousel"
                    data-bs-slide-to="<?= $slideIndex ?>"
                    class="<?= $slideIndex === 0 ? 'active' : '' ?>"
                    <?= $slideIndex === 0 ? 'aria-current="true"' : '' ?>
                    aria-label="Products slide <?= $slideIndex + 1 ?>"
                ></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($productSlides as $slideIndex => $slide): ?>
                <div class="carousel-item <?= $slideIndex === 0 ? 'active' : '' ?>">
                    <div class="product-grid">
                        <?php foreach ($slide as $product): ?>
                            <article class="product-card product-card-wrap">
                                <a href="<?= Html::encode(Url::to(['/site/product', 'id' => (int) $product['id']])) ?>" style="text-decoration: none; color: inherit; display: block;">
                                    <?php if (!empty($product['image'])): ?>
                                        <img
                                            src="<?= Html::encode(Url::to('@web/' . ltrim($product['image'], '/'))) ?>"
                                            class="product-image"
                                            alt="<?= Html::encode($product['name']) ?>"
                                            loading="lazy"
                                            draggable="false"
                                        >
                                    <?php else: ?>
                                        <div class="product-image"></div>
                                    <?php endif; ?>
                                    <h3><?= Html::encode($product['name']) ?></h3>
                                    <p><?= number_format((float) $product['price'], 0, '', ' ') ?> ₽</p>
                                </a>
                                <?= $this->render('_favorite_btn', [
                                    'productId' => (int) $product['id'],
                                    'isFavorite' => in_array((int) $product['id'], $favoriteProductIds, true),
                                    'extraClass' => 'fav-btn--sm fav-btn--on-card',
                                ]) ?>
                            </article>
                        <?php endforeach; ?>
                        <?php for ($i = count($slide); $i < 4; $i++): ?>
                            <article class="product-card">
                                <div class="product-image"></div>
                                <h3>Название товара</h3>
                                <p>0 ₽</p>
                            </article>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev products-control" type="button" data-bs-target="#productsCarousel" data-bs-slide="prev" aria-label="Previous">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next products-control" type="button" data-bs-target="#productsCarousel" data-bs-slide="next" aria-label="Next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    </div>
</section>

<section class="block about">
    <div class="about-text">
        <h2>TheMarket</h2>
        <p>
            Платформа для покупки и продажи эксклюзивных вещей от независимых брендов.<br>
            Только ограниченные серии и авторские работы.<br>
            TheMarket - пространство авторской формы.<br>
            Независимые бренды. Ограниченные серии. Реальные идеи.
        </p>
    </div>
    <div class="about-image"></div>
</section>

<section class="block community" style="position:relative;">
    <h2>TheMarket - это fashion-сообщество</h2>
    <div class="community-title">TheMarket</div>
    <div class="community-grid">
        <article>
            <h3>01 - Покупатель</h3>
            <p>• Регистрация<br>• Выбор вещи<br>• Оформление заказа</p>
        </article>
        <article>
            <h3>02 - Бренд</h3>
            <p>• Регистрация бренда<br>• Добавление товаров<br>• Продажи</p>
        </article>
        <article>
            <h3>03 - Платформа</h3>
            <p>• Комиссия<br>• Модерация<br>• Поддержка</p>
        </article>
    </div>
</section>

<section class="block why">
    <h2>ПОЧЕМУ АВТОРСКАЯ ОДЕЖДА</h2>
    <div class="why-grid">
        <article>
            <div class="why-icon">
                <img src="images/why-unique.svg" alt="Уникальность" width="48" height="48" loading="lazy" style="display:block;margin:0 auto;" />
            </div>
            <h3>Уникальность</h3>
            <p>Каждое изделие создается в ограниченном количестве. Вы не встретите такое же на каждом углу.</p>
        </article>
        <article>
            <div class="why-icon">
                <img src="images/why-quality.svg" alt="Качество" width="48" height="48" loading="lazy" style="display:block;margin:0 auto;" />
            </div>
            <h3>Качество</h3>
            <p>Тщательный отбор материалов и ручная работа гарантируют долговечность каждой вещи.</p>
        </article>
        <article>
            <div class="why-icon">
                <img src="images/why-support.svg" alt="Поддержка дизайнеров" width="48" height="48" loading="lazy" style="display:block;margin:0 auto;" />
            </div>
            <h3>Поддержка дизайнеров</h3>
            <p>Покупая авторскую одежду, вы поддерживаете независимых дизайнеров и их творчество.</p>
        </article>
    </div>
</section>

<section class="block brands">
    <div class="block-head">
        <h2>Бренды</h2>
        <a href="<?= Url::to(['/site/brands']) ?>" class="pill">Все бренды</a>
    </div>

    <?php
    $brandData = $brands ?? [];
    $brandSlides = array_chunk($brandData, 4);
    ?>

    <div id="brandsCarousel" class="carousel slide brands-carousel" data-bs-ride="carousel" data-bs-interval="3500" data-bs-pause="false">
        <div class="carousel-inner">
            <?php foreach ($brandSlides as $slideIndex => $slide): ?>
                <div class="carousel-item <?= $slideIndex === 0 ? 'active' : '' ?>">
                    <div class="brand-row">
                        <?php foreach ($slide as $brand): ?>
                            <div class="brand-item">
                                <img src="<?= Html::encode(Url::to('@web/' . ltrim($brand['logo'] ?? 'images/brand-placeholder.jpg', '/'))) ?>" alt="<?= Html::encode($brand['name']) ?>" width="168" height="112" loading="lazy" draggable="false" />
                            </div>
                        <?php endforeach; ?>

                        <?php for ($i = count($slide); $i < 4; $i++): ?>
                            <div class="brand-item brand-item--empty"></div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="block cta">
    <div class="cta-box text-center">
        <h2 class="cta-title mb-3">Платформа начинается с профиля.</h2>
        <p class="cta-subtitle mb-4">
            Зарегистрируйтесь, покупайте эксклюзивные вещи<br>
            и при желании создайте собственный бренд.
        </p>
        <a class="btn cta-btn" href="<?= Url::to(['/site/login']) ?>">войти / регистрация</a>
        <div class="cta-divider my-4"></div>
        <div class="cta-footer text-start">04 — Присоединись к name</div>
    </div>
</section>

<?php
$this->registerJs(<<<JS
(() => {
    const carouselElement = document.getElementById('mainHeroCarousel');
    if (!carouselElement || !window.bootstrap || !window.bootstrap.Carousel) {
        return;
    }

    const carousel = bootstrap.Carousel.getOrCreateInstance(carouselElement, {
        interval: 5000,
        ride: 'carousel',
        pause: false,
        touch: true
    });
    carousel.cycle();

    let startX = 0;
    let isDragging = false;
    const dragThreshold = 40;
    const isControlClick = (target) =>
        Boolean(target.closest('.carousel-control-prev, .carousel-control-next, .carousel-indicators'));

    carouselElement.addEventListener('dragstart', (event) => event.preventDefault());

    carouselElement.addEventListener('pointerdown', (event) => {
        if (event.pointerType !== 'mouse') {
            return;
        }
        if (event.button !== 0) {
            return;
        }
        if (isControlClick(event.target)) {
            return;
        }

        isDragging = true;
        startX = event.clientX;
        carouselElement.setPointerCapture(event.pointerId);
    });

    carouselElement.addEventListener('pointerup', (event) => {
        if (!isDragging) {
            return;
        }
        if (isControlClick(event.target)) {
            isDragging = false;
            carouselElement.releasePointerCapture(event.pointerId);
            return;
        }

        const distance = event.clientX - startX;
        isDragging = false;
        carouselElement.releasePointerCapture(event.pointerId);

        if (Math.abs(distance) < dragThreshold) {
            return;
        }

        if (distance > 0) {
            carousel.prev();
            carousel.cycle();
            return;
        }

        carousel.next();
        carousel.cycle();
    });

    carouselElement.addEventListener('pointercancel', () => {
        isDragging = false;
    });
})();
JS);
?>
