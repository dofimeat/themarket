<?php
/** @var yii\web\View $this */
/** @var array $product */
/** @var array $images */
/** @var array $sizes */
/** @var array $recommended */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = (string) ($product['name'] ?? 'Товар');
$images = $images ?? [];
$sizes = $sizes ?? [];
$recommended = $recommended ?? [];

$brandName = trim((string) ($product['brand_name'] ?? ''));
$brandLogo = trim((string) ($product['brand_logo'] ?? ''));
?>

<div class="home-wrap product-view-wrap">
    <section class="product-centered">
        <div class="product-main">
            <!-- Image Section -->
            <div class="product-media">
                <div id="productCarousel" class="carousel slide product-carousel" data-bs-ride="false" data-bs-touch="true">
                    <div class="carousel-inner">
                        <?php foreach ($images as $idx => $src): ?>
                            <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                                <img
                                    class="product-photo"
                                    src="<?= Html::encode(Url::to('@web/' . ltrim((string) $src, '/'))) ?>"
                                    alt="<?= Html::encode((string) ($product['name'] ?? '')) ?>"
                                    loading="<?= $idx === 0 ? 'eager' : 'lazy' ?>"
                                    draggable="false"
                                >
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="product-indicators">
                        <?php foreach ($images as $idx => $_): ?>
                            <button
                                type="button"
                                data-bs-target="#productCarousel"
                                data-bs-slide-to="<?= (int) $idx ?>"
                                class="<?= $idx === 0 ? 'active' : '' ?>"
                                <?= $idx === 0 ? 'aria-current="true"' : '' ?>
                                aria-label="Slide <?= (int) $idx + 1 ?>"
                            ></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Info Section -->
            <div class="product-info-wrap">
                <h1 class="product-name"><?= Html::encode((string) ($product['name'] ?? '')) ?></h1>
                <div class="product-price"><?= number_format((float) ($product['price'] ?? 0), 0, '', ' ') ?>Р</div>

                <div class="product-field">
                    <div class="product-label">Размер:</div>
                    <select class="form-select product-select" aria-label="Размер">
                        <?php if (empty($sizes)): ?>
                            <option selected>—</option>
                        <?php else: ?>
                            <?php foreach ($sizes as $size): ?>
                                <option><?= Html::encode((string) $size) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <button class="btn product-add" type="button">Добавить в корзину</button>

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

        <!-- Recommendations Section -->
        <?php if (!empty($recommended)): ?>
            <div class="product-reco" style="width: 100%; margin-top: 120px; border-top: 1px solid #F0F0F0; padding-top: 64px;">
                <div class="product-reco-title" style="font-size: 14px; color: #999; margin-bottom: 32px;">Вам может понравиться</div>
                <div class="product-grid">
                    <?php foreach ($recommended as $rec): ?>
                        <a class="product-card" href="<?= Html::encode(Url::to(['/site/product', 'id' => (int) $rec['id']])) ?>" style="text-decoration: none; color: inherit;">
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
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>
