<?php
namespace Sil\Idp\IdSync\console\controllers;

use Sil\Idp\IdSync\common\sync\Synchronizer;
use Yii;
use yii\console\Controller;

class BatchController extends Controller
{
    public function actionFull()
    {
        $synchronizer = new Synchronizer(Yii::$app->idStore, Yii::$app->idBroker);
        $synchronizer->syncAll();
    }
    
    /**
     * Run an incremental sync, slightly overlapping with the time frame of the
     * previous sync.
     */
    public function actionIncremental()
    {
        $synchronizer = new Synchronizer(Yii::$app->idStore, Yii::$app->idBroker);
        $synchronizer->syncUsersChangedSince(strtotime('-11 minutes'));
    }
}
