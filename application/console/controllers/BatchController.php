<?php

namespace Sil\Idp\IdSync\console\controllers;

use Sil\Idp\IdSync\common\traits\SyncProvider;
use yii\console\Controller;

class BatchController extends Controller
{
    use SyncProvider;

    public function actionFull()
    {
        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncAllNotifyException();
    }

    /**
     * Run an incremental sync, slightly overlapping with the time frame of the
     * previous sync.
     */
    public function actionIncremental()
    {
        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncUsersChangedSince(strtotime('-11 minutes'));
    }
}
