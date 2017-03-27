<?php
namespace frontend\controllers;

use frontend\components\BaseRestController;
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
        
        /** @todo Check system status. Return 200 OK if okay. */
        
    }

    public function actionUndefinedRequest()
    {
        throw new NotFoundHttpException();
    }
}
