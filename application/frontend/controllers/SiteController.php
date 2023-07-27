<?php

namespace Sil\Idp\IdSync\frontend\controllers;

use Exception;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\NotifierInterface;
use Sil\Idp\IdSync\frontend\components\BaseRestController;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException as Exception500;
use yii\web\NotFoundHttpException;

class SiteController extends BaseRestController
{
    public const HttpExceptionBadGateway = 502;

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
        // report OK (200) as long as this service is running
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionUndefinedRequest()
    {
        throw new NotFoundHttpException();
    }
}
