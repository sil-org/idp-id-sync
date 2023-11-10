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
        $start_time = microtime(true);

        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncAllNotifyException();

        captureCheckIn(
            slug: Yii::$app->params['sentryMonitorSlug'],
            status: CheckInStatus::ok(),
            duration: microtime(true) - $start_time,
        );
    }

    /**
     * Run an incremental sync, slightly overlapping with the time frame of the
     * previous sync.
     */
    public function actionIncremental()
    {
        $start_time = microtime(true);

        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncUsersChangedSince(strtotime('-11 minutes'));

        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncAllNotifyException();

        captureCheckIn(
            slug: Yii::$app->params['sentryMonitorSlug'],
            status: CheckInStatus::ok(),
            duration: microtime(true) - $start_time,
        );
    }
}
