<?php
/** @var yii\web\View $this */
/** @var array $reviews */
/** @var yii\data\Pagination $pages */
/** @var string $statusFilter */
/** @var string $search */

use app\models\ProductReview;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Отзывы';

$statusLabels = ProductReview::statusLabels();
?>
<div class="admin-wrap">
    <div class="admin-head">
        <h1 class="admin-title">Отзывы</h1>
        <a class="admin-back" href="<?= Html::encode(Url::to(['/admin'])) ?>">&larr; Назад</a>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="admin-flash admin-flash--success"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
    <?php endif; ?>

    <form class="admin-search-form" action="<?= Html::encode(Url::to(['/admin/reviews'])) ?>" method="get">
        <input
            type="text"
            name="search"
            class="admin-search-input"
            placeholder="Поиск по тексту, товару, email..."
            value="<?= Html::encode($search) ?>"
        >
        <button type="submit" class="admin-search-btn">Найти</button>
    </form>

    <div class="admin-filter-pills">
        <a href="<?= Html::encode(Url::to(['/admin/reviews'])) ?>"
           class="admin-filter-pill <?= $statusFilter === '' ? 'admin-filter-pill--active' : '' ?>">Все</a>
        <?php foreach ($statusLabels as $key => $label): ?>
            <a href="<?= Html::encode(Url::to(['/admin/reviews', 'status' => $key])) ?>"
               class="admin-filter-pill <?= $statusFilter === $key ? 'admin-filter-pill--active' : '' ?>"><?= Html::encode($label) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($reviews)): ?>
        <div class="admin-empty">Отзывов не найдено.</div>
    <?php else: ?>
        <!-- Desktop table -->
        <div class="admin-table-wrap admin-reviews-desktop">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Товар</th>
                        <th>Пользователь</th>
                        <th>Рейтинг</th>
                        <th>Текст</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $r): ?>
                        <?php
                        $st = (string) ($r['status'] ?? 'pending');
                        $userName = trim(($r['user_first_name'] ?? '') . ' ' . ($r['user_last_name'] ?? ''));
                        if ($userName === '') {
                            $userName = $r['user_email'] ?? '—';
                        }
                        $stars = str_repeat('★', (int) ($r['rating'] ?? 5)) . str_repeat('☆', 5 - (int) ($r['rating'] ?? 5));
                        ?>
                        <tr>
                            <td>#<?= (int) ($r['id'] ?? 0) ?></td>
                            <td>
                                <?php if (!empty($r['product_id'])): ?>
                                    <a href="<?= Html::encode(Url::to(['/site/product', 'id' => (int) $r['product_id']])) ?>" target="_blank">
                                        <?= Html::encode($r['product_name'] ?? '—') ?>
                                    </a>
                                <?php else: ?>
                                    <?= Html::encode($r['product_name'] ?? '—') ?>
                                <?php endif; ?>
                            </td>
                            <td><?= Html::encode($userName) ?></td>
                            <td class="admin-review-stars"><?= $stars ?></td>
                            <td class="admin-review-text-cell"><?= Html::encode(mb_strimwidth($r['text'] ?? '', 0, 100, '...')) ?></td>
                            <td>
                                <?php
                                $badgeClass = match ($st) {
                                    'pending' => 'admin-badge--new',
                                    'approved' => 'admin-badge--paid',
                                    'rejected' => 'admin-badge--cancelled',
                                    default => '',
                                };
                                ?>
                                <span class="admin-badge <?= $badgeClass ?>"><?= Html::encode($statusLabels[$st] ?? $st) ?></span>
                            </td>
                            <td><?php
                                $created = $r['created_at'] ?? null;
                                echo $created !== null && $created !== ''
                                    ? Html::encode(Yii::$app->formatter->asDatetime($created, 'php:d.m.Y'))
                                    : '—';
                            ?></td>
                            <td class="admin-review-actions">
                                <?php if ($st !== 'approved'): ?>
                                    <a href="<?= Html::encode(Url::to(['/admin/review-approve', 'id' => (int) $r['id']])) ?>"
                                       class="admin-btn-small admin-btn-small--approve"
                                       title="Одобрить">✓</a>
                                <?php endif; ?>
                                <?php if ($st !== 'rejected'): ?>
                                    <a href="<?= Html::encode(Url::to(['/admin/review-reject', 'id' => (int) $r['id']])) ?>"
                                       class="admin-btn-small admin-btn-small--reject"
                                       title="Отклонить">✗</a>
                                <?php endif; ?>
                                <a href="<?= Html::encode(Url::to(['/admin/review-delete', 'id' => (int) $r['id']])) ?>"
                                   class="admin-btn-small admin-btn-small--delete"
                                   title="Удалить"
                                   onclick="return confirm('Удалить этот отзыв?');">🗑</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile cards -->
        <div class="admin-reviews-mobile">
            <?php foreach ($reviews as $r): ?>
                <?php
                $st = (string) ($r['status'] ?? 'pending');
                $userName = trim(($r['user_first_name'] ?? '') . ' ' . ($r['user_last_name'] ?? ''));
                if ($userName === '') {
                    $userName = $r['user_email'] ?? '—';
                }
                $stars = str_repeat('★', (int) ($r['rating'] ?? 5)) . str_repeat('☆', 5 - (int) ($r['rating'] ?? 5));
                $badgeClass = match ($st) {
                    'pending' => 'admin-badge--new',
                    'approved' => 'admin-badge--paid',
                    'rejected' => 'admin-badge--cancelled',
                    default => '',
                };
                ?>
                <div class="admin-review-card card-like">
                    <div class="admin-review-card-head">
                        <span class="admin-review-card-product">
                            <?php if (!empty($r['product_id'])): ?>
                                <a href="<?= Html::encode(Url::to(['/site/product', 'id' => (int) $r['product_id']])) ?>" target="_blank">
                                    <?= Html::encode($r['product_name'] ?? '—') ?>
                                </a>
                            <?php else: ?>
                                <?= Html::encode($r['product_name'] ?? '—') ?>
                            <?php endif; ?>
                        </span>
                        <span class="admin-badge <?= $badgeClass ?>"><?= Html::encode($statusLabels[$st] ?? $st) ?></span>
                    </div>
                    <div class="admin-review-card-user"><?= Html::encode($userName) ?></div>
                    <div class="admin-review-card-stars"><?= $stars ?></div>
                    <p class="admin-review-card-text"><?= Html::encode($r['text'] ?? '') ?></p>
                    <div class="admin-review-card-foot">
                        <span><?php
                            $created = $r['created_at'] ?? null;
                            echo $created !== null && $created !== ''
                                ? Html::encode(Yii::$app->formatter->asDatetime($created, 'php:d.m.Y'))
                                : '—';
                        ?></span>
                        <div class="admin-review-actions">
                            <?php if ($st !== 'approved'): ?>
                                <a href="<?= Html::encode(Url::to(['/admin/review-approve', 'id' => (int) $r['id']])) ?>"
                                   class="admin-btn-small admin-btn-small--approve">✓ Одобрить</a>
                            <?php endif; ?>
                            <?php if ($st !== 'rejected'): ?>
                                <a href="<?= Html::encode(Url::to(['/admin/review-reject', 'id' => (int) $r['id']])) ?>"
                                   class="admin-btn-small admin-btn-small--reject">✗ Отклонить</a>
                            <?php endif; ?>
                            <a href="<?= Html::encode(Url::to(['/admin/review-delete', 'id' => (int) $r['id']])) ?>"
                               class="admin-btn-small admin-btn-small--delete"
                               onclick="return confirm('Удалить этот отзыв?');">🗑 Удалить</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($pages->pageCount > 1): ?>
            <?= LinkPager::widget([
                'pagination' => $pages,
                'options' => ['class' => 'admin-pager'],
            ]) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
