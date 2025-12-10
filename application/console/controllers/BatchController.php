<?php

namespace Sil\Idp\IdSync\console\controllers;

use Sentry\CheckInStatus;
use Sil\Idp\IdSync\common\components\Monitor;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
use Sil\Idp\IdSync\common\interfaces\NotifierInterface;
use Sil\Idp\IdSync\common\sync\Synchronizer;
use Sil\Psr3Adapters\Psr3Yii2Logger;
use Yii;
use yii\console\Controller;

use function Sentry\captureCheckIn;

class BatchController extends Controller
{
    public function actionFull()
    {
        $sentryMonitorSlug = Yii::$app->params['sentryMonitorSlug'];
        if ($sentryMonitorSlug !== "") {
            $checkInId = captureCheckIn(
                slug: $sentryMonitorSlug,
                status: CheckInStatus::inProgress()
            );
        }

        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncAllNotifyException();

        if ($sentryMonitorSlug != "") {
            captureCheckIn(
                slug: $sentryMonitorSlug,
                status: CheckInStatus::ok(),
                checkInId: $checkInId,
            );
        }

        /* @var $monitor Monitor */
        $monitor = Yii::$app->monitor;
        $monitor->Heartbeat();
    }

    /**
     * Run an incremental sync, slightly overlapping with the time frame of the
     * previous sync.
     */
    public function actionIncremental()
    {
        $sentryMonitorSlug = Yii::$app->params['sentryMonitorSlug'];
        if ($sentryMonitorSlug !== "") {
            $checkInId = captureCheckIn(
                slug: $sentryMonitorSlug,
                status: CheckInStatus::inProgress()
            );
        }

        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncUsersChangedSince(strtotime('-11 minutes'));

        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncAllNotifyException();

        if ($sentryMonitorSlug != "") {
            captureCheckIn(
                slug: $sentryMonitorSlug,
                status: CheckInStatus::ok(),
                checkInId: $checkInId,
            );
        }

        /* @var $monitor Monitor */
        $monitor = Yii::$app->monitor;
        $monitor->Heartbeat();
    }

    protected function getSynchronizer()
    {
        /* @var $idStore IdStoreInterface */
        $idStore = Yii::$app->idStore;

        /* @var $idBroker IdBrokerInterface */
        $idBroker = Yii::$app->idBroker;

        /* @var $notifier NotifierInterface */
        $notifier = Yii::$app->notifier;

        $logger = new Psr3Yii2Logger();
        $syncSafetyCutoff = Yii::$app->params['syncSafetyCutoff'];

        $enableNewUserNotification = Yii::$app->params['enableNewUserNotification'] ?? false;

        return new Synchronizer(
            $idStore,
            $idBroker,
            $logger,
            $notifier,
            $syncSafetyCutoff,
            $enableNewUserNotification
        );
    }
}
