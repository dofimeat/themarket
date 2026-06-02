<?php
/** @var yii\web\View $this */
/** @var app\models\CheckoutForm $model */
/** @var array $cartItems */
/** @var float $cartTotal */
/** @var float $deliveryCost */
/** @var float $grandTotal */

use app\widgets\Alert;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Оформление заказа';
?>
<div class="home-wrap">
    <?= Alert::widget() ?>

    <a href="<?= Html::encode(Url::to(['/cart'])) ?>" class="checkout-back">← Вернуться в корзину</a>
    <h1 class="cart-page-title">Оформление заказа</h1>

    <?php $form = ActiveForm::begin([
        'id' => 'checkout-form',
        'options' => ['class' => 'checkout-form'],
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}",
            'labelOptions' => ['class' => 'checkout-field-label'],
            'inputOptions' => ['class' => 'form-control checkout-input'],
            'errorOptions' => ['class' => 'checkout-field-error'],
        ],
    ]); ?>

    <div class="checkout-layout">
        <!-- Left: Form -->
        <div class="checkout-form-col">
            <div class="checkout-section card-like">
                <div class="checkout-section-title">Контактные данные</div>
                <div class="checkout-fields-grid">
                    <?= $form->field($model, 'email')->textInput(['placeholder' => 'email@example.com']) ?>
                    <?= $form->field($model, 'phone')->textInput([
                        'placeholder' => '+7 (___) ___-__-__',
                        'id' => 'checkout-phone',
                        'autocomplete' => 'tel',
                    ]) ?>
                </div>
                <div class="checkout-fields-grid">
                    <?= $form->field($model, 'last_name')->textInput(['placeholder' => 'Иванов']) ?>
                    <?= $form->field($model, 'first_name')->textInput(['placeholder' => 'Иван']) ?>
                </div>
            </div>

            <div class="checkout-section card-like">
                <div class="checkout-section-title">Доставка</div>
                <div class="checkout-fields-grid">
                    <?= $form->field($model, 'country')->textInput(['placeholder' => 'Россия']) ?>
                    <?= $form->field($model, 'city')->textInput(['placeholder' => 'Санкт-Петербург']) ?>
                </div>
                <?= $form->field($model, 'address')->textInput(['placeholder' => 'ул. Примерная, д. 67, кв. 67']) ?>
                <div class="checkout-fields-grid">
                    <?= $form->field($model, 'postal_code')->textInput(['placeholder' => '123456']) ?>
                </div>

                <div class="checkout-delivery-options">
                    <label class="checkout-delivery-option">
                        <input type="radio" name="CheckoutForm[delivery_method]" value="courier" <?= $model->delivery_method === 'courier' ? 'checked' : '' ?>>
                        <span class="checkout-delivery-label">
                            <span class="checkout-delivery-name">Курьер</span>
                            <span class="checkout-delivery-desc">Доставка до двери</span>
                        </span>
                    </label>
                    <label class="checkout-delivery-option">
                        <input type="radio" name="CheckoutForm[delivery_method]" value="pickup" <?= $model->delivery_method === 'pickup' ? 'checked' : '' ?>>
                        <span class="checkout-delivery-label">
                            <span class="checkout-delivery-name">Самовывоз</span>
                            <span class="checkout-delivery-desc">Пункт выдачи</span>
                        </span>
                    </label>
                    <label class="checkout-delivery-option">
                        <input type="radio" name="CheckoutForm[delivery_method]" value="post" <?= $model->delivery_method === 'post' ? 'checked' : '' ?>>
                        <span class="checkout-delivery-label">
                            <span class="checkout-delivery-name">Почта</span>
                            <span class="checkout-delivery-desc">Почта России</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="checkout-section card-like">
                <div class="checkout-section-title">Оплата</div>
                <div class="checkout-payment-option">
                    <label class="checkout-delivery-option">
                        <input type="radio" name="CheckoutForm[payment_method]" value="card" checked>
                        <span class="checkout-delivery-label">
                            <span class="checkout-delivery-name">Банковская карта</span>
                            <span class="checkout-delivery-desc">Visa, MasterCard, МИР</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="checkout-section card-like">
                <div class="checkout-section-title">Комментарий</div>
                <?= $form->field($model, 'comment')->textarea([
                    'rows' => 3,
                    'class' => 'form-control checkout-textarea',
                    'placeholder' => 'Необязательное поле',
                ])->label(false) ?>
            </div>
        </div>

        <!-- Right: Summary -->
        <div class="checkout-summary-col">
            <div class="cart-summary checkout-summary-sticky">
                <div class="cart-summary-title">Ваш заказ</div>

                <?php foreach ($cartItems as $key => $item): ?>
                    <?php
                    $price = (float) ($item['price'] ?? 0);
                    $qty = (int) ($item['quantity'] ?? 1);
                    $image = (string) ($item['image'] ?? '');
                    $name = (string) ($item['name'] ?? '');
                    $size = (string) ($item['size'] ?? '');
                    ?>
                    <div class="checkout-summary-item">
                        <div class="checkout-summary-img">
                            <?php if ($image !== ''): ?>
                                <img src="<?= Html::encode(Url::to('@web/' . ltrim($image, '/'))) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <div class="cart-item-img-empty"></div>
                            <?php endif; ?>
                        </div>
                        <div class="checkout-summary-info">
                            <div class="checkout-summary-name"><?= Html::encode($name) ?></div>
                            <?php if ($size !== ''): ?>
                                <div class="checkout-summary-size"><?= Html::encode($size) ?></div>
                            <?php endif; ?>
                            <div class="checkout-summary-qty"><?= $qty ?> × <?= number_format($price, 0, '', ' ') ?> ₽</div>
                        </div>
                        <div class="checkout-summary-price"><?= number_format($price * $qty, 0, '', ' ') ?> ₽</div>
                    </div>
                <?php endforeach; ?>

                <div class="cart-summary-divider"></div>

                <div class="cart-summary-row">
                    <span>Товары</span>
                    <span><?= number_format($cartTotal, 0, '', ' ') ?> ₽</span>
                </div>
                <div class="cart-summary-row">
                    <span>Доставка</span>
                    <span><?= $deliveryCost > 0 ? number_format($deliveryCost, 0, '', ' ') . ' ₽' : 'Бесплатно' ?></span>
                </div>

                <div class="cart-summary-divider"></div>

                <div class="cart-summary-row cart-summary-row--total">
                    <span>Итого</span>
                    <span><?= number_format($grandTotal, 0, '', ' ') ?> ₽</span>
                </div>

                <?= Html::submitButton('Оплатить', ['class' => 'cart-summary-btn']) ?>

                <div class="checkout-legal-text">
                    Нажимая кнопку «Оплатить», вы соглашаетесь с
                    <a href="#">политикой конфиденциальности</a>,
                    <a href="#">условиями пользовательского соглашения</a> и
                    <a href="#">публичной офертой</a>.
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<script>
(function(){
    var input = document.getElementById('checkout-phone');
    if (!input) return;

    input.addEventListener('input', function(e){
        var x = input.value.replace(/\D/g, '');
        if (x.length > 0 && x[0] === '8') x = '7' + x.substring(1);
        if (x.length > 11) x = x.substring(0, 11);

        var formatted = '';
        if (x.length > 0) formatted = '+' + x[0];
        if (x.length > 1) formatted += ' (' + x.substring(1, 4);
        if (x.length >= 4) formatted += ') ';
        if (x.length >= 5) formatted += x.substring(4, 7);
        if (x.length >= 8) formatted += '-' + x.substring(7, 9);
        if (x.length >= 10) formatted += '-' + x.substring(9, 11);

        input.value = formatted;
    });

    input.addEventListener('focus', function(){
        if (!input.value) input.value = '+7 (';
    });

    input.addEventListener('keydown', function(e){
        if (e.key === 'Backspace' && input.value.length <= 4) {
            e.preventDefault();
            input.value = '';
        }
    });
})();
</script>
