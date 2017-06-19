<?php

namespace app\modules\asset\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\imagine\Image;
use app\models\Asset;
use app\components\helpers\Users;
use app\components\helpers\Data;

class ManageController extends \yii\web\Controller
{
    // Properties
    public $layout = '/commonLayout';
    public $breadcrumbItems;
    public $breadcrumbHomeItems;
    public $route_nav = 'asset';
    public $viewPath = 'app/modules/asset/views';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['add'],
                'rules' => [
                    [ // Pages that can be accessed when logged in
                        'allow'     => true,
                        'actions'   => ['add'],
                        'roles'     => ['@']
                    ]
                ],
                'denyCallback' => function ($rule, $action) {
                    if (Yii::$app->user->isGuest) {
                        $this->goHome();
                    } else {
                        $this->redirect(['/dashboard']);
                    }
                }
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
    *   init()
    *   Required initializations in page.
    */
    public function init()
    {
        if (Yii::$app->user->isGuest) {
            $label  = 'Login';
            $url  = '/login';
        } else {
            $label  = Yii::t('app', 'Dashboard');
            $url  = '/dashboard';
        }
        $this->breadcrumbHomeItems = [
                'label' => $label,
                'url'   => $url,
                'template' => '<li>{link}</li> ',
        ];
    }

    public function actionAdd()
    {
        $model = new Asset;

        if(Yii::$app->request->post()) {
            var_dump("test");exit;
        }


        return $this->render('upload', ['model' => $model]);
    }

}
