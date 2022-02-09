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
    const HttpExceptionBadGateway = 502;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['except'] = [
            // bypass authentication, i.e., public API
            'system-status'
        ];

        return $behaviors;
    }

    /**
     * @throws HttpException with status 502 (Bad Gateway) if any of the dependent services have a problem
     * @throws Exception500 if dependent services are misconfigured
     */
    public function actionSystemStatus()
    {
        /* @var $webApp \yii\web\Application */
        $webApp = \Yii::$app;

        try {
            /* @var $notifier NotifierInterface */
            $notifier = $webApp->get('notifier');
        } catch (Exception $e) {
            \Yii::error($e->getMessage());
            throw new Exception500("Check notifier component's configuration.");
        }

        $this->checkNotifierStatus($notifier);

        try {
            /* @var $idBroker IdBrokerInterface */
            $idBroker = $webApp->get('idBroker');
        } catch (Exception $e) {
            \Yii::error($e->getMessage());
            throw new Exception500("Check idBroker component's configuration.");
        }

        $this->checkIdBrokerStatus($idBroker);
    }

    /**
     * @throws HttpException with status 502 (Bad Gateway) if the Notifier has a problem
     */
    private function checkNotifierStatus($notifier)
    {
        try {
            $notifier->getSiteStatus();
        } catch (Exception $e) {
            \Yii::error($e->getMessage());
            throw new HttpException(self::HttpExceptionBadGateway, 'Problem with notifier. Is email service down?');
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
            \Yii::error($e->getMessage());
            throw new HttpException(self::HttpExceptionBadGateway, 'Problem with ID Broker service.');
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
