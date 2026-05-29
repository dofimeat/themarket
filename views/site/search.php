<?php
/** @var yii\web\View $this */
/** @var string $q */
/** @var array $products */
/** @var array $brands */
/** @var int[] $favoriteProductIds */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Brand;
use app\models\User;

$this->title = $q !== '' ? 'Поиск: ' . $q : 'Поиск';
$products = $products ?? [];
$brands = $brands ?? [];
$favoriteProductIds = array_map('intval', $favoriteProductIds ?? []);
$totalResults = count($products) + count($brands);
?>

<section class="block catalog">
    <div class="catalog-head">
        <h2 class="catalog-title">
            <?php if ($q !== ''): ?>
                Результаты по запросу «<?= Html::encode($q) ?>»
                <span class="search-count">(<?= $totalResults ?>)</span>
            <?php else: ?>
                Поиск товаров
            <?php endif; ?>
        </h2>
    </div>

    <?php if ($q !== '' && $totalResults === 0): ?>
        <div class="search-empty">
            <p>По запросу «<?= Html::encode($q) ?>» ничего не найдено.</p>
            <p>Попробуйте изменить формулировку или <a href="<?= Html::encode(Url::to(['/site/catalog'])) ?>">посмотрите каталог</a>.</p>
        </div>
    <?php elseif ($q === ''): ?>
        <div class="search-empty">
            <p>Введите запрос для поиска товаров.</p>
        </div>
    <?php else: ?>

        <?php if (!empty($brands)): ?>
            <div class="search-brands-section">
                <h3 class="search-section-title">Бренды</h3>
                <div class="search-brands-grid">
                    <?php foreach ($brands as $brand): ?>
                        <?php
                        $logoPath = Brand::resolveLogoPath($brand['logo'] ?? null);
                        $logoUrl = Url::to('@web/' . ltrim($logoPath, '/'));
                        ?>
                        <a class="search-brand-card" href="<?= Html::encode(Url::to(['/site/brand', 'id' => (int) $brand['id']])) ?>">
                            <img src="<?= Html::encode($logoUrl) ?>" alt="<?= Html::encode($brand['name'] ?? '') ?>" class="search-brand-logo" loading="lazy">
                            <div class="search-brand-name"><?= Html::encode($brand['name'] ?? '') ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($products)): ?>
            <div class="search-products-section">
                <h3 class="search-section-title">Товары</h3>
                <div class="catalog-grid">
            <?php foreach ($products as $product): ?>
                <div class="catalog-card-wrap">
                    <a class="catalog-card" href="<?= Html::encode(Url::to(['/site/product', 'id' => (int) $product['id']])) ?>">
                        <?php if (!empty($product['image'])): ?>
                            <img
                                class="catalog-image"
                                src="<?= Html::encode(Url::to('@web/' . ltrim($product['image'], '/'))) ?>"
                                alt="<?= Html::encode($product['name'] ?? '') ?>"
                                loading="lazy"
                                draggable="false"
                            >
                        <?php else: ?>
                            <div class="catalog-image catalog-image--empty"></div>
                        <?php endif; ?>

                        <div class="catalog-meta">
                            <div class="catalog-name"><?= Html::encode($product['name'] ?? '') ?></div>
                            <div class="catalog-price"><?= number_format((float) ($product['price'] ?? 0), 0, '', ' ') ?>₽</div>
                        </div>
                    </a>
                    <?= $this->render('_favorite_btn', [
                        'productId' => (int) $product['id'],
                        'isFavorite' => in_array((int) $product['id'], $favoriteProductIds, true),
                        'extraClass' => 'fav-btn--sm fav-btn--on-card',
                    ]) ?>
                </div>
            <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</section>
