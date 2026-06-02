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
use app\models\ProfileSettingsForm;
use app\models\UserFavorite;
use app\models\Brand;
use app\models\ProductImage;

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
                'only' => [
                    'logout',
                    'profile',
                    'toggle-favorite',
                ],
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
                    'toggle-favorite' => ['post'],
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
            ->where(['p.status' => 'published'])
            ->orderBy(['p.created_at' => SORT_DESC])
            ->limit(12)
            ->all();

        $brands = (new Query())
            ->select(['id', 'name', 'logo'])
            ->from('brands')
            ->where(['not', ['logo' => null]])
            ->andWhere(['status' => Brand::STATUS_APPROVED])
            ->andWhere(['is_blocked' => 0])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('index', [
            'products' => $products,
            'brands' => $brands,
            'favoriteProductIds' => $this->favoriteProductIds(),
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
            ->where(['p.status' => 'published'])
            ->orderBy(['p.created_at' => SORT_DESC])
            ->all();

        return $this->render('catalog', [
            'products' => $products,
            'favoriteProductIds' => $this->favoriteProductIds(),
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
            ->where(['status' => Brand::STATUS_APPROVED])
            ->andWhere(['is_blocked' => 0])
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
        $brandCols = ['id', 'name', 'description', 'logo'];
        $bSchema = Yii::$app->db->getTableSchema('{{%brands}}', true);
        if ($bSchema !== null && $bSchema->getColumn('city') !== null) {
            $brandCols[] = 'city';
        }
        if ($bSchema !== null && $bSchema->getColumn('banner_image') !== null) {
            $brandCols[] = 'banner_image';
        }
        if ($bSchema !== null && $bSchema->getColumn('banner_color') !== null) {
            $brandCols[] = 'banner_color';
        }

        $brandQuery = (new Query())
            ->select($brandCols)
            ->from('{{%brands}}')
            ->where(['id' => (int) $id]);

        if ($bSchema !== null && $bSchema->getColumn('status') !== null) {
            $brandQuery->andWhere(['status' => Brand::STATUS_APPROVED]);
        }
        if ($bSchema !== null && $bSchema->getColumn('is_blocked') !== null) {
            $brandQuery->andWhere(['is_blocked' => 0]);
        }

        $brand = $brandQuery->one();

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
            ->where(['p.brand_id' => (int) $id, 'p.status' => 'published'])
            ->orderBy(['p.created_at' => SORT_DESC])
            ->all();

        return $this->render('brand', [
            'brand' => $brand,
            'products' => $products,
            'favoriteProductIds' => $this->favoriteProductIds(),
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
                'brand_id' => 'b.id',
            ])
            ->from(['p' => 'products'])
            ->leftJoin(['b' => 'brands'], 'b.id = p.brand_id')
            ->where(['p.id' => (int) $id])
            ->one();

        if (!$product) {
            throw new NotFoundHttpException('Товар не найден.');
        }

        $brandId = (int) ($product['brand_id'] ?? 0);
        if ($brandId > 0) {
            $brand = Brand::findOne($brandId);
            if ($brand === null || $brand->status !== Brand::STATUS_APPROVED || $brand->isBlocked()) {
                throw new NotFoundHttpException('Товар не найден.');
            }
        }

        $imageOrder = array_merge(['is_main' => SORT_DESC], ProductImage::orderByColumns());

        $images = (new Query())
            ->select(['image'])
            ->from('product_images')
            ->where(['product_id' => (int) $id])
            ->orderBy($imageOrder)
            ->column();

        if (empty($images)) {
            $images = ['images/image-24.png'];
        }

        $sizes = (new Query())
            ->select(['size', 'quantity'])
            ->from('product_sizes')
            ->where(['product_id' => (int) $id])
            ->orderBy(['size' => SORT_ASC])
            ->all();

        $features = (new Query())
            ->select(['name', 'value'])
            ->from('product_features')
            ->where(['product_id' => (int) $id])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

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
            ->where(['p.status' => 'published'])
            ->andWhere(['<>', 'p.id', (int) $id])
            ->orderBy(new \yii\db\Expression('RAND()'))
            ->limit(4)
            ->all();

        return $this->render('product', [
            'product' => $product,
            'images' => $images,
            'sizes' => $sizes,
            'features' => $features,
            'recommended' => $recommended,
            'isFavorite' => $this->isProductFavorite((int) $id),
            'favoriteProductIds' => $this->favoriteProductIds(),
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
            $returnUrl = Yii::$app->request->post('returnUrl', Yii::$app->request->get('returnUrl'));
            $safe = $this->sanitizeReturnUrl($returnUrl);
            if ($safe !== null) {
                return $this->redirect($safe);
            }
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
        $user = Yii::$app->user->identity;
        if ($user === null) {
            return $this->redirect(['login']);
        }

        $tab = (string) Yii::$app->request->get('tab', 'overview');
        $allowedTabs = ['overview', 'orders', 'favorites', 'settings'];
        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'overview';
        }

        $settingsForm = new ProfileSettingsForm(['user' => $user]);
        if ($tab === 'settings' && Yii::$app->request->isPost) {
            if ($settingsForm->load(Yii::$app->request->post()) && $settingsForm->save()) {
                Yii::$app->session->setFlash('success', 'Изменения сохранены.');
                return $this->redirect(['profile', 'tab' => 'settings']);
            }
        }

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
            ->where(['p.status' => 'published'])
            ->orderBy(new Expression('RAND()'))
            ->limit(3)
            ->all();

        $uid = (int) $user->id;

        $orders = [];
        try {
            $orders = (new Query())
                ->select(['id', 'status', 'total', 'created_at'])
                ->from('orders')
                ->where(['user_id' => $uid])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
        } catch (\Throwable $e) {
            $orders = [];
        }

        $favoriteProducts = [];
        try {
            $favoriteProducts = (new Query())
                ->select([
                    'p.id',
                    'p.name',
                    'p.price',
                    'image' => 'COALESCE(main_img.image, any_img.image)',
                    'fav_at' => 'f.created_at',
                ])
                ->from(['f' => 'user_favorites'])
                ->innerJoin(['p' => 'products'], 'p.id = f.product_id')
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
                ->where(['f.user_id' => $uid])
                ->orderBy(['f.created_at' => SORT_DESC])
                ->all();
        } catch (\Throwable $e) {
            $favoriteProducts = [];
        }

        $favoriteCount = count($favoriteProducts);
        $activeOrdersCount = 0;
        foreach ($orders as $o) {
            $st = (string) ($o['status'] ?? '');
            if (!in_array($st, ['delivered', 'cancelled'], true)) {
                $activeOrdersCount++;
            }
        }

        $brandModel = Brand::findByUserId($uid);
        $sellerBrand = $brandModel !== null ? $brandModel->attributes : null;

        return $this->render('profile', [
            'tab' => $tab,
            'recommendedProducts' => $recommendedProducts,
            'orders' => $orders,
            'favoriteProducts' => $favoriteProducts,
            'settingsForm' => $settingsForm,
            'favoriteCount' => $favoriteCount,
            'activeOrdersCount' => $activeOrdersCount,
            'favoriteProductIds' => $this->favoriteProductIds(),
            'sellerBrand' => $sellerBrand,
        ]);
    }

    /**
     * Добавить или убрать товар из избранного (только для авторизованных).
     */
    public function actionToggleFavorite()
    {
        $productId = (int) Yii::$app->request->post('product_id');
        $returnUrl = Yii::$app->request->post('returnUrl');
        $redirectTo = $this->sanitizeReturnUrl($returnUrl) ?? ['/site/catalog'];

        if ($productId <= 0) {
            Yii::$app->session->setFlash('warning', 'Некорректный товар.');
            return $this->redirect($redirectTo);
        }

        $productExists = (new Query())
            ->from('products')
            ->where(['id' => $productId])
            ->exists();
        if (!$productExists) {
            Yii::$app->session->setFlash('warning', 'Товар не найден.');
            return $this->redirect($redirectTo);
        }

        $uid = (int) Yii::$app->user->id;

        try {
            $row = UserFavorite::find()->where(['user_id' => $uid, 'product_id' => $productId])->one();
            if ($row !== null) {
                $row->delete();
                Yii::$app->session->setFlash('info', 'Товар убран из избранного.');
            } else {
                $f = new UserFavorite();
                $f->user_id = $uid;
                $f->product_id = $productId;
                if (!$f->save()) {
                    Yii::$app->session->setFlash('warning', 'Не удалось обновить избранное.');
                } else {
                    Yii::$app->session->setFlash('success', 'Товар добавлен в избранное.');
                }
            }
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('danger', 'Таблица избранного недоступна. Выполните SQL-скрипт из папки sql.');
        }

        return $this->redirect($redirectTo);
    }

    /**
     * @return int[]
     */
    protected function favoriteProductIds(): array
    {
        if (Yii::$app->user->isGuest) {
            return [];
        }
        $uid = (int) Yii::$app->user->id;
        try {
            return array_map('intval', (new Query())
                ->select('product_id')
                ->from('user_favorites')
                ->where(['user_id' => $uid])
                ->column());
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function isProductFavorite(int $productId): bool
    {
        if (Yii::$app->user->isGuest || $productId <= 0) {
            return false;
        }
        try {
            return (new Query())
                ->from('user_favorites')
                ->where(['user_id' => (int) Yii::$app->user->id, 'product_id' => $productId])
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Разрешить только относительные пути приложения (без open redirect).
     *
     * @param mixed $url
     */
    protected function sanitizeReturnUrl($url): ?string
    {
        if (!is_string($url) || $url === '') {
            return null;
        }
        if (!str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return null;
        }
        return $url;
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

    /**
     * Search products by name.
     *
     * @return string
     */
    public function actionSearch()
    {
        $q = trim((string) Yii::$app->request->get('q', ''));

        $products = [];
        $brands = [];
        if ($q !== '') {
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
                    "any_img.id = (
                    SELECT MIN(pi.id)
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                )"
                )
                ->where(['p.status' => 'published'])
                ->andWhere(['like', 'p.name', $q])
                ->orderBy(['p.created_at' => SORT_DESC])
                ->all();

            $brands = (new Query())
                ->select(['id', 'name', 'description', 'logo'])
                ->from('brands')
                ->where(['like', 'name', $q])
                ->andWhere(['status' => Brand::STATUS_APPROVED])
                ->andWhere(['is_blocked' => 0])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
        }

        return $this->render('search', [
            'q' => $q,
            'products' => $products,
            'brands' => $brands,
            'favoriteProductIds' => $this->favoriteProductIds(),
        ]);
    }
}
