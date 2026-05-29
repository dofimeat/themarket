<?php
/** @var yii\web\View $this */
/** @var array $products */
/** @var int[] $favoriteProductIds */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Каталог';
$products = $products ?? [];
$favoriteProductIds = array_map('intval', $favoriteProductIds ?? []);
?>

<section class="block catalog">
    <div class="catalog-head">
        <h2 class="catalog-title">Все изделия</h2>
        <a class="catalog-sort" href="#">Сортировать</a>
    </div>

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
</section>
