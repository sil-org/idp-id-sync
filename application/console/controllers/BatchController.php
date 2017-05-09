<?php
namespace Sil\Idp\IdSync\console\controllers;

use Sil\Idp\IdSync\common\sync\Synchronizer;
use Sil\Psr3Adapters\Psr3Yii2Logger;
use Yii;
use yii\console\Controller;

class BatchController extends Controller
{
    protected function getSynchronizer()
    {
        return new Synchronizer(
            Yii::$app->idStore,
            Yii::$app->idBroker,
            new Psr3Yii2Logger()
        );
    }
    
    public function actionFull()
    {
        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncAll();
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
