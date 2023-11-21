<?php

namespace Sil\Idp\IdSync\console\controllers;

use Sentry\CheckInStatus;
use Sil\Idp\IdSync\common\traits\SyncProvider;
use Yii;
use yii\console\Controller;

use function Sentry\captureCheckIn;

class BatchController extends Controller
{
    use SyncProvider;

    public function actionFull()
    {
        $checkInId = captureCheckIn(
            slug: Yii::$app->params['sentryMonitorSlug'],
            status: CheckInStatus::inProgress()
        );

        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncAllNotifyException();

        captureCheckIn(
            slug: Yii::$app->params['sentryMonitorSlug'],
            status: CheckInStatus::ok(),
            checkInId: $checkInId,
        );
    }

    /**
     * Run an incremental sync, slightly overlapping with the time frame of the
     * previous sync.
     */
    public function actionIncremental()
    {
        $checkInId = captureCheckIn(
            slug: Yii::$app->params['sentryMonitorSlug'],
            status: CheckInStatus::inProgress()
        );

        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncUsersChangedSince(strtotime('-11 minutes'));

        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncAllNotifyException();

        captureCheckIn(
            slug: Yii::$app->params['sentryMonitorSlug'],
            status: CheckInStatus::ok(),
            checkInId: $checkInId,
        );
    }
}
