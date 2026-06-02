<?php
/** @var yii\web\View $this */
/** @var array $cartItems */
/** @var float $cartTotal */
/** @var int $cartCount */

use app\widgets\Alert;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Корзина';
?>
<div class="home-wrap">
    <?= Alert::widget() ?>

    <h1 class="cart-page-title">Корзина</h1>

    <?php if (empty($cartItems)): ?>
        <div class="cart-empty">
            <div class="cart-empty-icon">
                <img src="<?= Html::encode(Url::to('@web/images/Shopping_Basket.svg')) ?>" alt="" width="64" height="64">
            </div>
            <div class="cart-empty-text">Корзина пуста</div>
            <a href="<?= Html::encode(Url::to(['/site/catalog'])) ?>" class="cart-empty-btn">Перейти в каталог</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($cartItems as $key => $item): ?>
                    <?php
                    $productId = (int) $item['product_id'];
                    $size = (string) ($item['size'] ?? '');
                    $price = (float) ($item['price'] ?? 0);
                    $qty = (int) ($item['quantity'] ?? 1);
                    $image = (string) ($item['image'] ?? '');
                    $name = (string) ($item['name'] ?? '');
                    $lineTotal = $price * $qty;
                    ?>
                    <div class="cart-item" data-cart-key="<?= Html::encode($key) ?>">
                        <div class="cart-item-img">
                            <?php if ($image !== ''): ?>
                                <img src="<?= Html::encode(Url::to('@web/' . ltrim($image, '/'))) ?>" alt="<?= Html::encode($name) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="cart-item-img-empty"></div>
                            <?php endif; ?>
                        </div>

                        <div class="cart-item-info">
                            <a href="<?= Html::encode(Url::to(['/site/product', 'id' => $productId])) ?>" class="cart-item-name"><?= Html::encode($name) ?></a>
                            <?php if ($size !== ''): ?>
                                <div class="cart-item-size">Размер: <?= Html::encode($size) ?></div>
                            <?php endif; ?>
                            <div class="cart-item-price"><?= number_format($price, 0, '', ' ') ?> ₽</div>
                        </div>

                        <div class="cart-item-total">
                            <?= number_format($lineTotal, 0, '', ' ') ?> ₽
                        </div>

                        <button type="button" class="cart-item-remove" data-cart-remove aria-label="Удалить">×</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="cart-summary-title">Ваш заказ</div>

                <div class="cart-summary-row">
                    <span>Товары (<?= $cartCount ?>)</span>
                    <span data-cart-summary-items><?= number_format($cartTotal, 0, '', ' ') ?> ₽</span>
                </div>
                <div class="cart-summary-row">
                    <span>Доставка</span>
                    <span>Рассчитывается при оформлении</span>
                </div>

                <div class="cart-summary-divider"></div>

                <div class="cart-summary-row cart-summary-row--total">
                    <span>Итого</span>
                    <span data-cart-summary-total><?= number_format($cartTotal, 0, '', ' ') ?> ₽</span>
                </div>

                <a href="<?= Html::encode(Url::to(['/checkout'])) ?>" class="cart-summary-btn">Оформить заказ</a>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
(function(){
    var csrf = '<?= Yii::$app->request->csrfToken ?>';

    function fmt(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' \u20BD';
    }

    function refreshSummary() {
        var total = 0, count = 0;
        document.querySelectorAll('.cart-item').forEach(function(el){
            var priceText = el.querySelector('.cart-item-price').textContent;
            var price = parseFloat(priceText.replace(/[^\d.]/g,''));
            total += price;
            count += 1;
        });
        var si = document.querySelector('[data-cart-summary-items]');
        var st = document.querySelector('[data-cart-summary-total]');
        if (si) si.textContent = fmt(total);
        if (st) st.textContent = fmt(total);
        if (window.updateCartBadge) window.updateCartBadge();
    }

    function removeItem(key) {
        var fd = new FormData();
        fd.append('key', key);
        fd.append('_csrf', csrf);
        fetch('<?= Url::to(['/cart/remove']) ?>', {method:'POST', body:fd})
            .then(function(r){return r.json();})
            .then(function(d){
                if (d.count === 0) {
                    location.reload();
                } else {
                    var el = document.querySelector('[data-cart-key="'+key+'"]');
                    if (el) el.remove();
                    refreshSummary();
                }
            })
            .catch(function(){});
    }

    document.addEventListener('click', function(e){
        var item = e.target.closest('.cart-item');
        if (!item) return;
        var key = item.getAttribute('data-cart-key');
        if (!key) return;

        if (e.target.closest('[data-cart-remove]')) {
            item.style.opacity = '0.5';
            removeItem(key);
        }
    });
})();
</script>
