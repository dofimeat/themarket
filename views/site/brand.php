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
?>

<section class="brand-banner">
    <div class="home-wrap">
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
