<?php
/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'О проекте';
?>

<div class="home-wrap about-page">
    <div class="about-page-title">О проекте/</div>

    <section class="manifest-section">
        <h1 class="manifest-header">МАНИФЕСТ</h1>
        <p class="manifest-intro">
            Мы верим, что мода — это высшая форма самовыражения. В мире, насыщенном товарами массового потребления, истинная роскошь заключается в дефиците, мастерстве и истории, стоящей за одеждой.
        </p>

        <div class="manifest-image-container">
            <img src="<?= Url::to('@web/images/slader1.jpg') ?>" alt="Manifest" class="manifest-image" onerror="this.src='https://placehold.co/1126x280?text=1126x280'">
        </div>

        <div class="manifest-text-block">
            Наша платформа — это не просто маркетплейс; это курируемая экосистема для авангарда. Мы связываем взыскательных коллекционеров с независимыми дизайнерами, которые расширяют границы возможного в моде.
        </div>

        <div class="manifest-text-block">
            Мы отвергаем сезонную текучку индустрии. Вместо этого мы фокусируемся на вневременных вещах, которые бросают вызов условностям. От деконструированного кроя до экспериментального текстиля — каждый предмет на нашей платформе является произведением искусства.
        </div>
    </section>

    <section class="opportunities-section">
        <h2 class="opportunities-title">Themarket даёт возможность</h2>
        <div class="opportunities-grid-container">
            <div class="opportunities-intro">
                Станьте частью закрытого клуба. Получите ранний доступ к лимитированным дропам и возможность выставлять свои работы.
            </div>
            <div class="opportunities-grid">
                <div class="opportunity-card">
                    Продавать вещи напрямую без посредников
                </div>
                <div class="opportunity-card">
                    Представить свой бренд аудитории
                </div>
                <div class="opportunity-card">
                    Представить свой бренд аудитории
                </div>
                <a href="<?= Url::to(['/site/login']) ?>" class="opportunity-card registration">
                    Регистрация
                </a>
            </div>
        </div>
    </section>
</div>
