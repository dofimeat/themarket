<?php

namespace app\controllers;

use app\models\Brand;
use app\models\BrandRegisterForm;
use app\models\Product;
use app\models\ProductAddForm;
use app\models\ProductEditForm;
use app\models\ProductImage;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
/**
 * Кабинет продавца: бренд и товары.
 */
class SellerController extends Controller
{
    public function init()
    {
        parent::init();
        $this->setViewPath('@app/views/site');
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
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
                    'toggle-product-status' => ['post'],
                ],
            ],
        ];
    }

    public function actionRegisterBrand()
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            return $this->redirect(['/login']);
        }

        $uid = (int) $user->id;
        if (Brand::findByUserId($uid) !== null) {
            Yii::$app->session->setFlash('info', 'У вас уже зарегистрирован бренд.');
            return $this->redirect(['brand-dashboard']);
        }

        $model = new BrandRegisterForm();
        if ($model->load(Yii::$app->request->post()) && $model->save($uid)) {
            Yii::$app->session->setFlash('success', 'Бренд успешно зарегистрирован.');
            return $this->redirect(['brand-dashboard']);
        }

        return $this->render('register-brand', ['model' => $model]);
    }

    public function actionBrandDashboard()
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            return $this->redirect(['/login']);
        }

        $brand = Brand::findByUserId((int) $user->id);
        if ($brand === null) {
            return $this->redirect(['register-brand']);
        }

        $listTab = (string) Yii::$app->request->get('list', 'active');
        if (!in_array($listTab, ['active', 'archive'], true)) {
            $listTab = 'active';
        }

        $bid = (int) $brand->id;

        $productQuery = (new Query())
            ->select([
                'p.id',
                'p.name',
                'p.price',
                'p.status',
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
            ->where(['p.brand_id' => $bid]);

        if ($listTab === 'active') {
            $productQuery->andWhere(['p.status' => 'published']);
        } else {
            $productQuery->andWhere(['not', ['p.status' => 'published']]);
        }

        $dashboardProducts = $productQuery->orderBy(['p.id' => SORT_DESC])->all();

        $totalProducts = (int) Product::find()->where(['brand_id' => $bid])->count();
        $activeProductsCount = (int) Product::find()
            ->where(['brand_id' => $bid, 'status' => Product::STATUS_PUBLISHED])
            ->count();

        $brandRow = $brand->attributes;
        $fullDesc = (string) ($brandRow['description'] ?? '');
        $parts = preg_split('/\r\n\r\n|\n\s*\n/', $fullDesc, 2);
        $conceptText = trim($parts[0] ?? '');
        $historyText = isset($parts[1]) ? trim($parts[1]) : '';
        if ($historyText === '') {
            $historyText = $conceptText;
        }

        $yearFounded = '';
        if (!empty($brandRow['created_at'])) {
            $ts = strtotime((string) $brandRow['created_at']);
            if ($ts !== false) {
                $yearFounded = date('Y', $ts);
            }
        }

        return $this->render('brand-dashboard', [
            'brand' => $brandRow,
            'dashboardProducts' => $dashboardProducts,
            'listTab' => $listTab,
            'totalProducts' => $totalProducts,
            'activeProductsCount' => $activeProductsCount,
            'conceptText' => $conceptText,
            'historyText' => $historyText,
            'yearFounded' => $yearFounded,
        ]);
    }

    public function actionEditBrand()
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            return $this->redirect(['/login']);
        }
        $uid = (int) $user->id;

        $brand = Brand::findByUserId($uid);
        if ($brand === null) {
            return $this->redirect(['register-brand']);
        }

        $model = new BrandRegisterForm();
        $model->loadFromBrand($brand->attributes);

        if ($model->load(Yii::$app->request->post()) && $model->updateBrand((int) $brand->id, $uid)) {
            Yii::$app->session->setFlash('success', 'Данные бренда сохранены.');
            return $this->redirect(['brand-dashboard']);
        }

        return $this->render('edit-brand', ['model' => $model]);
    }

    public function actionAddProduct()
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            return $this->redirect(['/login']);
        }

        $brand = Brand::findByUserId((int) $user->id);
        if ($brand === null) {
            return $this->redirect(['register-brand']);
        }

        $model = new ProductAddForm();
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $productId = $model->saveProduct((int) $brand->id);
            if ($productId !== null) {
                Yii::$app->session->setFlash('success', 'Товар добавлен.');
                return $this->redirect(['edit-product', 'id' => $productId]);
            }
        }

        $model->newImageFiles = null;

        return $this->render('add-product', [
            'model' => $model,
            'brand' => $brand->attributes,
        ]);
    }

    public function actionEditProduct($id)
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            return $this->redirect(['/login']);
        }

        $brand = Brand::findByUserId((int) $user->id);
        if ($brand === null) {
            return $this->redirect(['register-brand']);
        }

        $productId = (int) $id;
        $product = Product::findOwnedByUser($productId, (int) $user->id);
        if ($product === null) {
            throw new NotFoundHttpException('Товар не найден.');
        }

        $model = new ProductEditForm();
        $model->productId = $productId;

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->existingImages = ProductImage::findForProduct($productId);

            if ($model->saveProduct((int) $brand->id)) {
                Yii::$app->session->setFlash('success', 'Товар сохранён.');
                return $this->redirect(['edit-product', 'id' => $productId]);
            }
        } elseif (!$model->loadFromProduct($productId, (int) $brand->id)) {
            throw new NotFoundHttpException('Товар не найден.');
        }

        // Файловое поле не должно получать массив UploadedFile как value
        $model->newImageFiles = null;

        return $this->render('edit-product', [
            'model' => $model,
            'brand' => $brand->attributes,
            'productStatus' => (string) ($product->status ?? Product::STATUS_PUBLISHED),
        ]);
    }

    public function actionToggleProductStatus($id): Response
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            return $this->redirect(['/login']);
        }

        $productId = (int) $id;
        $product = Product::findOwnedByUser($productId, (int) $user->id);
        if ($product === null) {
            throw new NotFoundHttpException('Товар не найден.');
        }

        $current = (string) ($product->status ?? Product::STATUS_PUBLISHED);

        // Determine next status based on current state
        if ($current === Product::STATUS_PUBLISHED) {
            // Published → Draft (archive by user)
            $next = Product::STATUS_DRAFT;
            $flashType = 'success';
            $flashMsg = 'Товар перенесён в архив.';
        } elseif ($current === Product::STATUS_DRAFT) {
            // Draft → Pending (submit for moderation, NOT directly to published)
            $next = Product::STATUS_PENDING;
            $flashType = 'success';
            $flashMsg = 'Товар отправлен на модерацию.';
        } else {
            // Pending or Rejected → cannot change status
            $next = $current;
            $flashType = 'warning';
            $flashMsg = $current === Product::STATUS_PENDING
                ? 'Товар находится на модерации. Изменение статуса недоступно.'
                : 'Товар отклонён. Обратитесь к администратору.';
        }

        $product->status = $next;
        $product->save(false);

        Yii::$app->session->setFlash($flashType, $flashMsg);

        // Determine which list to redirect to
        $list = 'active';
        if ($next === Product::STATUS_DRAFT) {
            $list = 'archive';
        } elseif ($next === Product::STATUS_PENDING || $next === Product::STATUS_REJECTED) {
            $list = 'pending';
        }

        return $this->redirect([
            'brand-dashboard',
            'list' => $list,
        ]);
    }
}
