<?php
namespace Sil\Idp\IdSync\common\traits;

use Sil\Idp\IdSync\common\components\notify\EmailServiceNotifier;
use Sil\Idp\IdSync\common\interfaces\IdBrokerInterface;
use Sil\Idp\IdSync\common\interfaces\IdStoreInterface;
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
        
        $logger = new Psr3Yii2Logger();
        
        $notifierParams = Yii::$app->params['notifier'];
        $emailTo = $notifierParams['emailTo'];
        if (empty($emailTo)) {
            $logger->warning(sprintf(
                'Missing the to (%s) email address, so HR notification emails '
                . 'will not be sent.',
                var_export($emailTo, true)
            ));
            $notifier = null;
        } else {
            $notifier = new EmailServiceNotifier(
                $emailTo,
                $notifierParams['organizationName'],
                $notifierParams['emailServiceConfig']
            );
        }
        
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
