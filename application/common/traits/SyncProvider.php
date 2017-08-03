<?php
namespace Sil\Idp\IdSync\common\traits;

use Sil\Idp\IdSync\common\components\notify\EmailNotifier;
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
        $emailFrom = $notifierParams['emailFrom'];
        if (empty($emailTo) || empty($emailFrom)) {
            $logger->warning(sprintf(
                'Missing either the to (%s) or from (%s) email address, so '
                . 'notification emails will not be sent.',
                var_export($emailTo, true),
                var_export($emailFrom, true)
            ));
            $notifier = null;
        } else {
            $notifier = new EmailNotifier(
                Yii::$app->mailer,
                $emailTo,
                $emailFrom,
                $notifierParams['organizationName'],
                $idStore->getIdStoreName()
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
