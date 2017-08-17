<?php
namespace Sil\Idp\IdSync\frontend\controllers;

use Exception;
use Sil\Idp\IdSync\frontend\components\BaseRestController;
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;

class SiteController extends BaseRestController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['except'] = [
            // bypass authentication, i.e., public API
            'system-status'
        ];

        return $behaviors;
    }

    public function actionSystemStatus()
    {
        try {
            \Yii::$app->notifier;
        } catch (Exception $e) {
            \Yii::error($e->getMessage());
            throw new ServerErrorHttpException(
                'Check notifier configuration.',
                1502822830
            );
        }        
    }

    public function actionUndefinedRequest()
    {
        throw new NotFoundHttpException();
    }
}
