<?php
/** @var yii\web\View $this */
/** @var array $brands */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Бренды';
$brands = $brands ?? [];
?>

<section class="block brands-page">
    <div class="brands-page-title">Бренды/</div>

    <div class="brands-page-grid">
        <?php foreach ($brands as $brand): ?>
            <?php
            $name = (string) ($brand['name'] ?? '');
            $desc = (string) ($brand['description'] ?? '');
            $logo = (string) ($brand['logo'] ?? '');
            $logoPath = $logo !== '' ? ltrim($logo, '/') : 'images/brand1.jpg';
            ?>
            <article class="brand-card">
                <div class="brand-card-media">
                    <img
                        src="<?= Html::encode(Url::to('@web/' . $logoPath)) ?>"
                        alt="<?= Html::encode($name) ?>"
                        loading="lazy"
                        draggable="false"
                        onerror="this.onerror=null;this.src='<?= Html::encode(Url::to('@web/images/brand1.jpg')) ?>';"
                    >
                </div>
                <div class="brand-card-body">
                    <div class="brand-card-name"><?= Html::encode($name) ?></div>
                    <div class="brand-card-desc"><?= Html::encode($desc) ?></div>
                    <a href="<?= Html::encode(Url::to(['/site/brand', 'id' => $brand['id']])) ?>" class="btn brand-card-btn">Смотреть</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
