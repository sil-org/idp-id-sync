<?php

namespace Sil\Idp\IdSync\console\controllers;

use Sentry\CheckInStatus;
use Sil\Idp\IdSync\common\components\Monitor;
use Sil\Idp\IdSync\common\traits\SyncProvider;
use Yii;
use yii\console\Controller;

use function Sentry\captureCheckIn;

class BatchController extends Controller
{
    use SyncProvider;

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
}
