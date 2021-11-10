<?php
namespace Sil\Idp\IdSync\frontend\controllers;

use Exception;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\NotifierInterface;
use Sil\Idp\IdSync\frontend\components\BaseRestController;
use yii\web\ServerErrorHttpException as Exception500;
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
        /* @var $webApp \yii\web\Application */
        $webApp = \Yii::$app;

        try {
            /* @var $notifier NotifierInterface */
            $notifier = $webApp->get('notifier');
        } catch (Exception $e) {
            \Yii::error($e->getMessage());
            throw new Exception500("Check notifier component's configuration.");
        }

        // This sends error emails to the dev team too often
        // $this->checkNotifierStatus($notifier);

        try {
            /* @var $idBroker IdBrokerInterface */
            $idBroker = $webApp->get('idBroker');
        } catch (Exception $e) {
            \Yii::error($e->getMessage());
            throw new Exception500("Check idBroker component's configuration.");
        }
        
        try {
            $idBroker->getSiteStatus();
        } catch (Exception $e) {
            \Yii::error($e->getMessage());
            throw new Exception500('Problem with ID Broker service.');
        }
    }

    private function checkNotifierStatus($notifier)
    {
        try {
            $notifier->getSiteStatus();
        } catch (Exception $e) {
            \Yii::error($e->getMessage());
            throw new Exception500('Problem with notifier. Is email service down?');
        }
    }

    public function actionUndefinedRequest()
    {
        throw new NotFoundHttpException();
    }
}
