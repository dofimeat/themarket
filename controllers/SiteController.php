<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\db\Query;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'profile'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $products = (new Query())
            ->select([
                'p.id',
                'p.name',
                'p.price',
                'image' => 'COALESCE(main_img.image, any_img.image)',
            ])
            ->from(['p' => 'products'])
            ->leftJoin(
                ['main_img' => 'product_images'],
                'main_img.product_id = p.id AND main_img.is_main = 1'
            )
            ->leftJoin(
                ['any_img' => 'product_images'],
                'any_img.id = (
                    SELECT MIN(pi.id)
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                )'
            )
            ->where(['p.status' => 'active'])
            ->orderBy(['p.created_at' => SORT_DESC])
            ->limit(12)
            ->all();

        $brands = (new Query())
            ->select(['id', 'name', 'logo'])
            ->from('brands')
            ->where(['not', ['logo' => null]])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('index', [
            'products' => $products,
            'brands' => $brands,
        ]);
    }

    /**
     * Catalog page (4 latest active products).
     *
     * @return string
     */
    public function actionCatalog()
    {
        $products = (new Query())
            ->select([
                'p.id',
                'p.name',
                'p.price',
                'image' => 'COALESCE(main_img.image, any_img.image)',
            ])
            ->from(['p' => 'products'])
            ->leftJoin(
                ['main_img' => 'product_images'],
                'main_img.product_id = p.id AND main_img.is_main = 1'
            )
            ->leftJoin(
                ['any_img' => 'product_images'],
                'any_img.id = (
                    SELECT MIN(pi.id)
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                )'
            )
            ->where(['p.status' => 'active'])
            ->orderBy(['p.created_at' => SORT_DESC])
            ->all();

        return $this->render('catalog', [
            'products' => $products,
        ]);
    }

    /**
     * Brands page.
     *
     * @return string
     */
    public function actionBrands()
    {
        $brands = (new Query())
            ->select(['id', 'name', 'description', 'logo'])
            ->from('brands')
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('brands', [
            'brands' => $brands,
        ]);
    }

    /**
     * Single brand page.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionBrand($id)
    {
        $brand = (new Query())
            ->select(['id', 'name', 'description', 'logo'])
            ->from('brands')
            ->where(['id' => (int) $id])
            ->one();

        if (!$brand) {
            throw new NotFoundHttpException('Бренд не найден.');
        }

        $products = (new Query())
            ->select([
                'p.id',
                'p.name',
                'p.price',
                'image' => 'COALESCE(main_img.image, any_img.image)',
            ])
            ->from(['p' => 'products'])
            ->leftJoin(
                ['main_img' => 'product_images'],
                'main_img.product_id = p.id AND main_img.is_main = 1'
            )
            ->leftJoin(
                ['any_img' => 'product_images'],
                'any_img.id = (
                    SELECT MIN(pi.id)
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                )'
            )
            ->where(['p.brand_id' => (int) $id, 'p.status' => 'active'])
            ->orderBy(['p.created_at' => SORT_DESC])
            ->all();

        return $this->render('brand', [
            'brand' => $brand,
            'products' => $products,
        ]);
    }

    /**
     * Product details page.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionProduct($id)
    {
        $product = (new Query())
            ->select([
                'p.id',
                'p.name',
                'p.description',
                'p.price',
                'brand_name' => 'b.name',
                'brand_logo' => 'b.logo',
            ])
            ->from(['p' => 'products'])
            ->leftJoin(['b' => 'brands'], 'b.id = p.brand_id')
            ->where(['p.id' => (int) $id])
            ->one();

        if (!$product) {
            throw new NotFoundHttpException('Товар не найден.');
        }

        $images = (new Query())
            ->select(['image'])
            ->from('product_images')
            ->where(['product_id' => (int) $id])
            ->orderBy(['is_main' => SORT_DESC, 'id' => SORT_ASC])
            ->column();

        if (empty($images)) {
            $images = ['images/image-24.png'];
        }

        $sizes = (new Query())
            ->select(['size'])
            ->from('product_sizes')
            ->where(['product_id' => (int) $id])
            ->andWhere(['>', 'quantity', 0])
            ->orderBy(['size' => SORT_ASC])
            ->column();

        $recommended = (new Query())
            ->select([
                'p.id',
                'p.name',
                'p.price',
                'image' => 'COALESCE(main_img.image, any_img.image)',
            ])
            ->from(['p' => 'products'])
            ->leftJoin(
                ['main_img' => 'product_images'],
                'main_img.product_id = p.id AND main_img.is_main = 1'
            )
            ->leftJoin(
                ['any_img' => 'product_images'],
                'any_img.id = (
                    SELECT MIN(pi.id)
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                )'
            )
            ->where(['p.status' => 'active'])
            ->andWhere(['<>', 'p.id', (int) $id])
            ->orderBy(new \yii\db\Expression('RAND()'))
            ->limit(4)
            ->all();

        return $this->render('product', [
            'product' => $product,
            'images' => $images,
            'sizes' => $sizes,
            'recommended' => $recommended,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['profile']);
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['profile']);
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Регистрация нового пользователя.
     *
     * @return Response|string
     */
    public function actionRegister()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['profile']);
        }

        $model = new RegisterForm();
        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            return $this->redirect(['profile']);
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }

    /**
     * Личный кабинет (после входа или регистрации).
     *
     * @return string
     */
    public function actionProfile()
    {
        $recommendedProducts = (new Query())
            ->select([
                'p.id',
                'p.name',
                'p.price',
                'image' => 'COALESCE(main_img.image, any_img.image)',
            ])
            ->from(['p' => 'products'])
            ->leftJoin(
                ['main_img' => 'product_images'],
                'main_img.product_id = p.id AND main_img.is_main = 1'
            )
            ->leftJoin(
                ['any_img' => 'product_images'],
                'any_img.id = (
                    SELECT MIN(pi.id)
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                )'
            )
            ->where(['p.status' => 'active'])
            ->orderBy(new Expression('RAND()'))
            ->limit(3)
            ->all();

        return $this->render('profile', [
            'recommendedProducts' => $recommendedProducts,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
