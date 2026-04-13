<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\helpers\Url;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="home-page">
    <header class="home-header">
        <div class="home-wrap">
            <a href="/" class="home-logo">TheMarket</a>
            <nav class="home-nav">
                <a href="<?= Html::encode(Url::to(['/site/catalog'])) ?>">КАТАЛОГ</a>
                <a href="<?= Html::encode(Url::to(['/site/brands'])) ?>">БРЕНДЫ</a>
                <a href="<?= Html::encode(Url::to(['/site/about'])) ?>">О ПРОЕКТЕ</a>
            </nav>
            <div class="home-actions">
                <a href="#" class="home-action-icon" aria-label="Поиск">◯</a>
                <?php if (Yii::$app->user->isGuest): ?>
                    <a href="<?= Html::encode(Url::to(['/site/login'])) ?>" class="home-action-icon" aria-label="Профиль">
                        <img src="<?= Html::encode(Url::to('@web/images/user.svg')) ?>" alt="" width="28" height="28" decoding="async">
                    </a>
                <?php else: ?>
                    <a href="<?= Html::encode(Url::to(['/site/profile'])) ?>" class="home-action-icon" aria-label="Профиль">
                        <img src="<?= Html::encode(Url::to('@web/images/user.svg')) ?>" alt="" width="28" height="28" decoding="async">
                    </a>
                <?php endif; ?>
                <a href="#" class="home-action-icon" aria-label="Корзина">
                    <img src="<?= Html::encode(Url::to('@web/images/Shopping_Basket.svg')) ?>" alt="" width="28" height="28" decoding="async">
                </a>
            </div>
        </div>
    </header>

    <?= $content ?>

    <footer class="home-footer">
        <div class="home-wrap">
            <div class="home-footer-wrap">
                <div class="home-footer-col">
                    <h4 class="home-footer-title">TheMarket</h4>
                    <p class="home-footer-text">Платформа для авторских брендов и лимитированных коллекций.</p>
                </div>
                <div class="home-footer-col">
                    <h4 class="home-footer-title">Каталог</h4>
                    <a class="home-footer-link" href="<?= Html::encode(Url::to(['/site/brands'])) ?>">Бренды</a>
                    <a class="home-footer-link" href="<?= Html::encode(Url::to(['/site/about'])) ?>">О проекте</a>
                    <a class="home-footer-link" href="#">FAQ</a>
                </div>
                <div class="home-footer-col">
                    <h4 class="home-footer-title">Контакты</h4>
                    <a class="home-footer-link" href="#">info@gmail.com</a>
                    <div class="home-footer-text mt-2">Наши в соц. сети</div>
                    <a class="home-footer-link" href="#" style="display:inline-flex; align-items:center; gap:8px;">
                        <span aria-hidden="true" style="width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;background:#1E90FF;color:#fff;font-size:12px;">✈</span>
                        <span>name</span>
                    </a>
                </div>
            </div>

            <div class="home-footer-divider" role="separator" aria-hidden="true"></div>

            <div class="home-footer-bottom">
                <div class="home-footer-muted">2026 TheMarket. Все права защищены</div>
                <div class="home-footer-muted home-footer-bottom-links">
                    <a class="home-footer-link muted" href="#">Пользовательское соглашение</a>
                    <span aria-hidden="true">•</span>
                    <a class="home-footer-link muted" href="#">Политика конфиденциальности</a>
                </div>
            </div>
        </div>
    </footer>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
