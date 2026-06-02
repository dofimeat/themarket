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
            <a href="<?= Html::encode(Url::to(['/'])) ?>" class="home-logo">TheMarket</a>
            <nav class="home-nav" id="main-nav">
                <!-- <a href="<?= Html::encode(Url::to(['/'])) ?>">ГЛАВНАЯ</a> -->
                <a href="<?= Html::encode(Url::to(['/site/catalog'])) ?>">КАТАЛОГ</a>
                <a href="<?= Html::encode(Url::to(['/site/brands'])) ?>">БРЕНДЫ</a>
                <a href="<?= Html::encode(Url::to(['/site/about'])) ?>">О ПРОЕКТЕ</a>
                <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin()): ?>
                    <a href="<?= Html::encode(Url::to(['/admin'])) ?>">АДМИН</a>
                <?php endif; ?>
            </nav>
            <div class="home-actions">
                <div class="home-search-wrap">
                    <form class="home-search-form" action="<?= Html::encode(Url::to(['/site/search'])) ?>" method="get">
                        <input
                            type="text"
                            name="q"
                            class="home-search-input"
                            placeholder="Поиск товаров..."
                            value="<?= Html::encode(Yii::$app->request->get('q', '')) ?>"
                            autocomplete="off"
                        >
                        <button type="submit" class="home-search-submit" aria-label="Найти">
                            <img src="<?= Html::encode(Url::to('@web/images/Search Streamline Guidance – Free.svg')) ?>" alt="" width="20" height="20" decoding="async">
                        </button>
                    </form>
                    <a href="#" class="home-action-icon home-search-toggle" aria-label="Поиск">
                        <img src="<?= Html::encode(Url::to('@web/images/Search Streamline Guidance – Free.svg')) ?>" alt="Поиск" width="28" height="28" decoding="async">
                    </a>
                </div>
                <?php if (Yii::$app->user->isGuest): ?>
                    <a href="<?= Html::encode(Url::to(['/site/login'])) ?>" class="home-action-icon home-action-profile" aria-label="Профиль">
                        <img src="<?= Html::encode(Url::to('@web/images/user.svg')) ?>" alt="" width="28" height="28" decoding="async">
                    </a>
                <?php else: ?>
                    <a href="<?= Html::encode(Url::to(['/site/profile'])) ?>" class="home-action-icon home-action-profile" aria-label="Профиль">
                        <img src="<?= Html::encode(Url::to('@web/images/user.svg')) ?>" alt="" width="28" height="28" decoding="async">
                    </a>
                <?php endif; ?>
                <a href="<?= Html::encode(Url::to(['/cart'])) ?>" class="home-action-icon home-action-cart home-cart-link" aria-label="Корзина">
                    <img src="<?= Html::encode(Url::to('@web/images/Shopping_Basket.svg')) ?>" alt="" width="28" height="28" decoding="async">
                    <span class="home-cart-badge" id="cart-badge" style="display:none;"></span>
                </a>
                <button type="button" class="home-hamburger" id="hamburger-btn" aria-label="Меню">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile nav overlay -->
    <div class="mobile-nav-overlay" id="mobile-nav-overlay">
        <div class="mobile-nav-panel">
            <div class="mobile-nav-header">
            <a href="<?= Html::encode(Url::to(['/'])) ?>" class="home-logo">TheMarket</a>
                <button type="button" class="mobile-nav-close" id="mobile-nav-close" aria-label="Закрыть">×</button>
            </div>
            <nav class="mobile-nav-links">
                <!-- <a href="<?= Html::encode(Url::to(['/'])) ?>">Главная</a> -->
                <a href="<?= Html::encode(Url::to(['/site/catalog'])) ?>">Каталог</a>
                <a href="<?= Html::encode(Url::to(['/site/brands'])) ?>">Бренды</a>
                <a href="<?= Html::encode(Url::to(['/site/about'])) ?>">О проекте</a>
                <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin()): ?>
                    <a href="<?= Html::encode(Url::to(['/admin'])) ?>">Админ</a>
                <?php endif; ?>
            </nav>
            <div class="mobile-nav-footer">
                <!-- Cart: first -->
                <a href="<?= Html::encode(Url::to(['/cart'])) ?>" class="mobile-nav-auth">
                    <img src="<?= Html::encode(Url::to('@web/images/Shopping_Basket.svg')) ?>" alt="" width="20" height="20" style="vertical-align:middle;margin-right:8px;">Корзина
                    <span class="mobile-cart-badge" id="mobile-cart-badge" style="display:none;margin-left:8px;background:#FF3D00;color:#fff;border-radius:999px;min-width:20px;height:20px;padding:0 6px;font-size:12px;align-items:center;justify-content:center;"></span>
                </a>
                <?php if (Yii::$app->user->isGuest): ?>
                    <a href="<?= Html::encode(Url::to(['/site/login'])) ?>" class="mobile-nav-auth">
                        <img src="<?= Html::encode(Url::to('@web/images/user.svg')) ?>" alt="" width="20" height="20" style="vertical-align:middle;margin-right:8px;">Войти
                    </a>
                    <a href="<?= Html::encode(Url::to(['/site/register'])) ?>" class="mobile-nav-auth mobile-nav-auth--outline">Регистрация</a>
                <?php else: ?>
                    <a href="<?= Html::encode(Url::to(['/site/profile'])) ?>" class="mobile-nav-auth">
                        <img src="<?= Html::encode(Url::to('@web/images/user.svg')) ?>" alt="" width="20" height="20" style="vertical-align:middle;margin-right:8px;">Личный кабинет
                    </a>
                    <form method="post" action="<?= Html::encode(Url::to(['/site/logout'])) ?>" style="margin:0;">
                        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                        <button type="submit" class="mobile-nav-auth mobile-nav-auth--outline" style="width:100%;cursor:pointer;">Выйти</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

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
                    <a class="home-footer-link" href="<?= Html::encode(Url::to(['/site/faq'])) ?>">FAQ</a>
                </div>
                <div class="home-footer-col">
                    <h4 class="home-footer-title">Контакты</h4>
                    <a class="home-footer-link" href="#">info@gmail.com</a>
                    <div class="home-footer-text mt-2">Наши в соц. сети</div>
                    <a class="home-footer-link" href="https://t.me/dofvwiya" target="_blank" rel="noopener" style="display:inline-flex; align-items:center; gap:8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21.5 4.5L2.5 12.5L8 14.5L17 8L11 15L17.5 20L21.5 4.5Z" fill="#229ED9"/>
                        </svg>
                        <span>@dofvwiya</span>
                    </a>
                </div>
            </div>

            <div class="home-footer-divider" role="separator" aria-hidden="true"></div>

            <div class="home-footer-bottom">
                <div class="home-footer-muted">2026 TheMarket. Все права защищены</div>
                <div class="home-footer-muted home-footer-bottom-links">
                    <a class="home-footer-link muted" href="<?= Html::encode(Url::to(['/site/terms'])) ?>">Пользовательское соглашение</a>
                    <span aria-hidden="true">•</span>
                    <a class="home-footer-link muted" href="<?= Html::encode(Url::to(['/site/privacy'])) ?>">Политика конфиденциальности</a>
                </div>
            </div>
        </div>
    </footer>
</div>

<?php $this->endBody() ?>
<script>
(function(){
    var toggle = document.querySelector('.home-search-toggle');
    var wrap = document.querySelector('.home-search-wrap');
    if (toggle && wrap) {
        toggle.addEventListener('click', function(e){
            e.preventDefault();
            wrap.classList.toggle('is-open');
            if (wrap.classList.contains('is-open')) {
                wrap.querySelector('.home-search-input').focus();
            }
        });
        document.addEventListener('click', function(e){
            if (wrap.classList.contains('is-open') && !wrap.contains(e.target)) {
                wrap.classList.remove('is-open');
            }
        });
    }

    // Hamburger menu
    var hamburger = document.getElementById('hamburger-btn');
    var overlay = document.getElementById('mobile-nav-overlay');
    var closeBtn = document.getElementById('mobile-nav-close');
    function openNav() {
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function closeNav() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    }
    if (hamburger) hamburger.addEventListener('click', openNav);
    if (closeBtn) closeBtn.addEventListener('click', closeNav);
    if (overlay) overlay.addEventListener('click', function(e){
        if (e.target === overlay) closeNav();
    });
})();
</script>
<script>
(function(){
    function updateCartBadge() {
        var badge = document.getElementById('cart-badge');
        var mobileBadge = document.getElementById('mobile-cart-badge');
        fetch('<?= Html::encode(Url::to(['/cart/count'])) ?>')
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (d.count > 0) {
                    var label = d.count > 99 ? '99+' : d.count;
                    if (badge) { badge.textContent = label; badge.style.display = 'flex'; }
                    if (mobileBadge) { mobileBadge.textContent = label; mobileBadge.style.display = 'inline-flex'; }
                } else {
                    if (badge) { badge.style.display = 'none'; }
                    if (mobileBadge) { mobileBadge.style.display = 'none'; }
                }
            })
            .catch(function(){});
    }
    updateCartBadge();
    window.updateCartBadge = updateCartBadge;
})();
</script>
</body>
</html>
<?php $this->endPage() ?>
