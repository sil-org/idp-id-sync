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
     * @throws HttpException with status 502 (Bad Gateway) if the Notifier has a problem
     */
    private function checkNotifierStatus($notifier)
    {
        try {
            $notifier->getSiteStatus();
        } catch (Exception $e) {
            throw new HttpException(
                self::HttpExceptionBadGateway,
                'Problem with notifier. Is email service down? : ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * @throws HttpException with status 502 (Bad Gateway) if the ID Broker has a problem
     */
    private function checkIdBrokerStatus($idBroker)
    {
        try {
            $idBroker->getSiteStatus();
        } catch (Exception $e) {
            throw new HttpException(
                self::HttpExceptionBadGateway,
                'Problem with ID Broker service: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionUndefinedRequest()
    {
        throw new NotFoundHttpException();
    }
}
