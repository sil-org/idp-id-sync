<?php
namespace Sil\Idp\IdSync\common\traits;

use Sil\Idp\IdSync\common\components\notify\EmailServiceNotifier;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
use Sil\Idp\IdSync\common\interfaces\NotifierInterface;
use Sil\Idp\IdSync\common\sync\Synchronizer;
use Sil\Psr3Adapters\Psr3Yii2Logger;
use Yii;

/**
 * Trait for providing the ability to get a Synchronizer.
 */
trait SyncProvider
{
    /**
     * Get a fully-configured Synchronizer.
     *
     * @return Synchronizer
     */
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
        
        return new Synchronizer(
            $idStore,
            $idBroker,
            $logger,
            $notifier,
            $syncSafetyCutoff
        );
    }
}
