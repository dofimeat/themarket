<?php

/** @var yii\web\View $this */
/** @var int $productId */
/** @var bool $isFavorite */
/** @var string $extraClass */

use yii\helpers\Html;
use yii\helpers\Url;

$productId = (int) $productId;
$isFavorite = (bool) ($isFavorite ?? false);
$extraClass = trim((string) ($extraClass ?? ''));
$btnClass = trim('fav-btn ' . ($isFavorite ? 'fav-btn--on' : '') . ' ' . $extraClass);
$returnUrl = Yii::$app->request->url;
?>
<?php if (Yii::$app->user->isGuest): ?>
    <a
        href="<?= Html::encode(Url::to(['/site/login', 'returnUrl' => $returnUrl])) ?>"
        class="<?= Html::encode($btnClass) ?> fav-btn--guest"
        aria-label="Войти, чтобы добавить в избранное"
        title="Войти, чтобы добавить в избранное"
    >♥</a>
<?php else: ?>
    <?= Html::beginForm(['/site/toggle-favorite'], 'post', ['class' => 'fav-form']) ?>
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
        <?= Html::hiddenInput('product_id', (string) $productId) ?>
        <?= Html::hiddenInput('returnUrl', $returnUrl) ?>
        <button
            type="submit"
            class="<?= Html::encode($btnClass) ?>"
            aria-label="<?= $isFavorite ? 'Убрать из избранного' : 'Добавить в избранное' ?>"
            title="<?= $isFavorite ? 'В избранном' : 'В избранное' ?>"
        >♥</button>
    <?= Html::endForm() ?>
<?php endif; ?>
