<?php
/** @var yii\web\View $this */
/** @var array $products */
/** @var array $availableSizes */
/** @var array $availableBrands */
/** @var array $priceRange */
/** @var array $filterSizes */
/** @var array $filterBrands */
/** @var string|int $priceMin */
/** @var string|int $priceMax */
/** @var string $sort */
/** @var int $activeFilterCount */
/** @var int[] $favoriteProductIds */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Каталог';
$products = $products ?? [];
$availableSizes = $availableSizes ?? [];
$availableBrands = $availableBrands ?? [];
$priceRange = $priceRange ?? ['min_price' => 0, 'max_price' => 100000];
$filterSizes = (array) ($filterSizes ?? []);
$filterBrands = (array) ($filterBrands ?? []);
$priceMin = $priceMin ?? '';
$priceMax = $priceMax ?? '';
$sort = (string) ($sort ?? 'newest');
$activeFilterCount = (int) ($activeFilterCount ?? 0);
$favoriteProductIds = array_map('intval', $favoriteProductIds ?? []);

$floorPrice = (int) floor((float) ($priceRange['min_price'] ?? 0));
$ceilPrice = (int) ceil((float) ($priceRange['max_price'] ?? 100000));

$sortOptions = [
    'newest' => 'Сначала новые',
    'price_asc' => 'Цена: по возрастанию',
    'price_desc' => 'Цена: по убыванию',
    'name' => 'По названию',
];

/**
 * Build catalog URL with current filter params, optionally overriding one.
 */
$buildUrl = function (array $override = [], array $remove = []) use ($sort, $filterSizes, $filterBrands, $priceMin, $priceMax) {
    $params = [];
    $currentSort = $override['sort'] ?? $sort;
    if ($currentSort !== 'newest') {
        $params['sort'] = $currentSort;
    }
    $sizes = $override['size'] ?? $filterSizes;
    if (!in_array('size', $remove, true)) {
        foreach ((array) $sizes as $s) {
            $params['size'][] = $s;
        }
    }
    $brands = $override['brand'] ?? $filterBrands;
    if (!in_array('brand', $remove, true)) {
        foreach ((array) $brands as $b) {
            $params['brand'][] = $b;
        }
    }
    $pMin = array_key_exists('price_min', $override) ? $override['price_min'] : $priceMin;
    $pMax = array_key_exists('price_max', $override) ? $override['price_max'] : $priceMax;
    if (!in_array('price_min', $remove, true) && $pMin !== '' && (float) $pMin > 0) {
        $params['price_min'] = $pMin;
    }
    if (!in_array('price_max', $remove, true) && $pMax !== '' && (float) $pMax > 0) {
        $params['price_max'] = $pMax;
    }
    return Url::to(array_merge(['/site/catalog'], $params));
};
?>

<section class="block catalog">
    <div class="catalog-head">
        <h2 class="catalog-title">Все изделия</h2>
        <div class="catalog-head-right">
            <?php if ($activeFilterCount > 0): ?>
                <a class="catalog-clear-filters" href="<?= Html::encode(Url::to(['/site/catalog'])) ?>">
                    Сбросить фильтры
                    <span class="catalog-filter-badge"><?= $activeFilterCount ?></span>
                </a>
            <?php endif; ?>
            <div class="catalog-sort-wrap">
                <label class="catalog-sort-label" for="catalog-sort-select">Сортировать:</label>
                <select id="catalog-sort-select" class="catalog-sort-select">
                    <?php foreach ($sortOptions as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="catalog-layout">
        <!-- Filter sidebar -->
        <aside class="catalog-filters" id="catalog-filters">
            <div class="catalog-filters-header">
                <span class="catalog-filters-title">Фильтры</span>
                <button type="button" class="catalog-filters-close" id="filters-close-btn" aria-label="Закрыть">×</button>
            </div>

            <form id="catalog-filter-form" method="get" action="<?= Html::encode(Url::to(['/site/catalog'])) ?>">
                <input type="hidden" name="sort" value="<?= Html::encode($sort) ?>">

                <!-- Size filter -->
                <?php if (!empty($availableSizes)): ?>
                    <div class="filter-group">
                        <div class="filter-group-title">Размер</div>
                        <div class="filter-group-options">
                            <?php foreach ($availableSizes as $sizeVal): ?>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="size[]" value="<?= Html::encode($sizeVal) ?>"
                                        <?= in_array((string) $sizeVal, array_map('strval', $filterSizes), true) ? 'checked' : '' ?>>
                                    <span class="filter-checkbox-mark"></span>
                                    <span class="filter-checkbox-label"><?= Html::encode($sizeVal) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Price filter -->
                <div class="filter-group">
                    <div class="filter-group-title">Цена</div>
                    <div class="filter-price-range">
                        <div class="filter-price-field">
                            <label class="filter-price-label">от</label>
                            <input type="number" name="price_min" class="filter-price-input"
                                value="<?= Html::encode($priceMin) ?>"
                                placeholder="<?= $floorPrice ?>" min="0">
                        </div>
                        <span class="filter-price-sep">—</span>
                        <div class="filter-price-field">
                            <label class="filter-price-label">до</label>
                            <input type="number" name="price_max" class="filter-price-input"
                                value="<?= Html::encode($priceMax) ?>"
                                placeholder="<?= $ceilPrice ?>" min="0">
                        </div>
                    </div>
                    <div class="filter-price-hint"><?= number_format($floorPrice, 0, '', ' ') ?> — <?= number_format($ceilPrice, 0, '', ' ') ?> ₽</div>
                </div>

                <!-- Brand filter -->
                <?php if (!empty($availableBrands)): ?>
                    <div class="filter-group">
                        <div class="filter-group-title">Бренд</div>
                        <div class="filter-group-options">
                            <?php foreach ($availableBrands as $brandRow): ?>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="brand[]" value="<?= (int) $brandRow['id'] ?>"
                                        <?= in_array((int) $brandRow['id'], array_map('intval', $filterBrands), true) ? 'checked' : '' ?>>
                                    <span class="filter-checkbox-mark"></span>
                                    <span class="filter-checkbox-label"><?= Html::encode($brandRow['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn catalog-filter-apply">Применить</button>
            </form>
        </aside>

        <!-- Mobile filter toggle -->
        <button type="button" class="catalog-mobile-filter-btn" id="mobile-filter-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/>
                <circle cx="6" cy="12" r="2" fill="currentColor"/><circle cx="14" cy="6" r="2" fill="currentColor"/><circle cx="10" cy="18" r="2" fill="currentColor"/>
            </svg>
            Фильтры
            <?php if ($activeFilterCount > 0): ?>
                <span class="catalog-filter-badge"><?= $activeFilterCount ?></span>
            <?php endif; ?>
        </button>

        <!-- Products -->
        <div class="catalog-products">
            <?php if (empty($products)): ?>
                <div class="catalog-empty">
                    <div class="catalog-empty-text">Товары не найдены</div>
                    <a href="<?= Html::encode(Url::to(['/site/catalog'])) ?>" class="catalog-empty-link">Сбросить фильтры</a>
                </div>
            <?php else: ?>
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
                                    <div class="catalog-price"><?= number_format((float) ($product['price'] ?? 0), 0, '', ' ') ?> ₽</div>
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
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
(function(){
    // Sort select
    var sortSelect = document.getElementById('catalog-sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function(){
            var url = new URL(window.location.href);
            url.searchParams.set('sort', sortSelect.value);
            window.location.href = url.toString();
        });
    }

    // Mobile filter toggle
    var mobileBtn = document.getElementById('mobile-filter-btn');
    var filtersPanel = document.getElementById('catalog-filters');
    var closeBtn = document.getElementById('filters-close-btn');
    if (mobileBtn && filtersPanel) {
        mobileBtn.addEventListener('click', function(){
            filtersPanel.classList.add('catalog-filters--open');
            document.body.style.overflow = 'hidden';
        });
    }
    if (closeBtn && filtersPanel) {
        closeBtn.addEventListener('click', function(){
            filtersPanel.classList.remove('catalog-filters--open');
            document.body.style.overflow = '';
        });
    }
})();
</script>
