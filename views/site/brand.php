<?php
/** @var yii\web\View $this */
/** @var array $brand */
/** @var array $products */
/** @var int[] $favoriteProductIds */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $brand['name'] ?? 'Бренд';
$products = $products ?? [];
$favoriteProductIds = array_map('intval', $favoriteProductIds ?? []);

// Banner: image > color > default
$bannerImage = trim((string) ($brand['banner_image'] ?? ''));
$bannerColor = trim((string) ($brand['banner_color'] ?? ''));
$logoPath = trim((string) ($brand['logo'] ?? ''));

$bannerStyle = '';
$bannerClass = 'brand-banner';
if ($bannerImage !== '') {
    $bannerStyle = 'background-image: url(' . Html::encode(Url::to('@web/' . ltrim($bannerImage, '/'))) . '); background-size: cover; background-position: center;';
    $bannerClass .= ' brand-banner--image';
} elseif ($bannerColor !== '') {
    $bannerStyle = 'background-color: ' . Html::encode($bannerColor) . ';';
    $bannerClass .= ' brand-banner--color';
}
?>

<section class="<?= $bannerClass ?>" style="<?= $bannerStyle ?>">
    <div class="home-wrap brand-banner-inner">
        <?php if ($logoPath !== '' && $logoPath !== \app\models\User::DEFAULT_AVATAR): ?>
            <img
                src="<?= Html::encode(Url::to('@web/' . ltrim($logoPath, '/'))) ?>"
                alt="<?= Html::encode($brand['name'] ?? '') ?>"
                class="brand-banner-logo"
                loading="lazy"
            >
        <?php endif; ?>
        <h1 class="brand-banner-title"><?= Html::encode($brand['name'] ?? '') ?></h1>
    </div>
</section>

<div class="home-wrap">
    <section class="brand-concept-card">
        <div class="concept-grid">
            <div class="concept-label">Концепция</div>
            <div class="concept-text">
                <?= Html::encode($brand['description'] ?? '') ?>
            </div>
        </div>
        <div class="concept-info">
            <?php
            $city = trim((string) ($brand['city'] ?? ''));
            ?>
            <div class="concept-city"><?= $city !== '' ? Html::encode($city) : 'Город не указан' ?></div>
            <div class="concept-views">Просмотры 0</div>
        </div>
        <div class="concept-divider"></div>
    </section>

    <section class="brand-products-section">
        <div class="catalog-head">
            <h2 class="catalog-title">Все товары</h2>
            <a class="catalog-sort" href="#">Сортировать</a>
        </div>

        <div class="catalog-grid">
            <?php if (empty($products)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;">
                    У этого бренда пока нет товаров.
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="catalog-card-wrap">
                        <a class="brand-product-card" href="<?= Html::encode(Url::to(['/site/product', 'id' => (int) $product['id']])) ?>">
                            <?php if (!empty($product['image'])): ?>
                                <img
                                    class="brand-product-image"
                                    src="<?= Html::encode(Url::to('@web/' . ltrim($product['image'], '/'))) ?>"
                                    alt="<?= Html::encode($product['name'] ?? '') ?>"
                                    loading="lazy"
                                    draggable="false"
                                >
                            <?php else: ?>
                                <div class="brand-product-image catalog-image--empty"></div>
                            <?php endif; ?>

                            <div class="catalog-meta">
                                <div class="brand-product-name"><?= Html::encode($product['name'] ?? '') ?></div>
                                <div class="brand-product-price"><?= number_format((float) ($product['price'] ?? 0), 0, '', ' ') ?>₽</div>
                            </div>
                        </a>
                        <?= $this->render('_favorite_btn', [
                            'productId' => (int) $product['id'],
                            'isFavorite' => in_array((int) $product['id'], $favoriteProductIds, true),
                            'extraClass' => 'fav-btn--sm fav-btn--on-card',
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>
