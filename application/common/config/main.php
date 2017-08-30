<?php

use Sil\Idp\IdSync\common\components\IdBrokerBase;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\components\notify\EmailServiceNotifier;
use Sil\Idp\IdSync\common\components\notify\NullNotifier;
use Sil\JsonLog\target\JsonSyslogTarget;
use Sil\PhpEnv\Env;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

$alertsEmail = Env::get('ALERTS_EMAIL');
$appEnv = Env::get('APP_ENV', 'prod'); // Have default match "application/frontend/web/index.php".
$idpName = Env::requireEnv('IDP_NAME');
$idpDisplayName = Env::get('IDP_DISPLAY_NAME', $idpName);

$idBrokerOptionalConfig = Env::getArrayFromPrefix('ID_BROKER_CONFIG_');
$idBrokerOptionalConfig['trustedIpRanges'] = Env::getArray('ID_BROKER_CONFIG_trustedIpRanges');
$idStoreOptionalConfig = Env::getArrayFromPrefix('ID_STORE_CONFIG_');

$emailServiceConfig = Env::getArrayFromPrefix('EMAIL_SERVICE_');

// Re-retrieve the validIpRanges as an array.
$emailServiceConfig['validIpRanges'] = Env::getArray('EMAIL_SERVICE_validIpRanges');

$hrNotifierEmailTo = Env::get('NOTIFIER_EMAIL_TO');
if (empty($hrNotifierEmailTo)) {
    $notifierConfig = ['class' => NullNotifier::class];
} else {
    /* Configure the notifier, used to send notifications to HR (such as
     * when users lack an email address):  */
    $notifierConfig = [
        'class' => EmailServiceNotifier::class,
        'emailServiceConfig' => $emailServiceConfig,
        'emailTo' => $hrNotifierEmailTo,
        'organizationName' => $idpDisplayName,
    ];
}

return [
    'id' => $idpName,
    'bootstrap' => ['log'],
    'components' => [
        
        'idBroker' => ArrayHelper::merge([
            'class' => IdBrokerBase::getAdapterClassFor(
                Env::get('ID_BROKER_ADAPTER')
            ),
        ], $idBrokerOptionalConfig),
        
        'idStore' => ArrayHelper::merge([
            'class' => IdStoreBase::getAdapterClassFor(
                Env::get('ID_STORE_ADAPTER')
            ),
        ], $idStoreOptionalConfig),
        
        'log' => [
            'targets' => [
                [
                    'class' => JsonSyslogTarget::class,
                    'categories' => ['application'],
                    
                    // Disable logging of _SERVER, _POST, etc.
                    'logVars' => [],
                    
                    'prefix' => function ($message) use ($appEnv, $idpName) {
                        return Json::encode([
                            'app_env' => $appEnv,
                            'idp_name' => $idpName,
                        ]);
                    },
                ],
                [
                    'class' => EmailServiceTarget::class,
                    'categories' => ['application'], // stick to messages from this app, not all of Yii's built-in messaging.
                    'enabled' => !empty($alertsEmail),
                    'except' => [
                        'yii\web\HttpException:400',
                        'yii\web\HttpException:401',
                        'yii\web\HttpException:404',
                        'yii\web\HttpException:409',
                        'yii\web\HttpException:422',
                        'Sil\EmailService\Client\EmailServiceClientException',
                    ],
                    'levels' => ['error'],
                    'logVars' => [], // Disable logging of _SERVER, _POST, etc.
                    'message' => [
                        'to' => $alertsEmail,
                        'subject' => 'ERROR - ' . $idpName . ' ID Sync [' . $appEnv .']',
                    ],
                    'baseUrl' => $emailServiceConfig['baseUrl'],
                    'accessToken' => $emailServiceConfig['accessToken'],
                    'assertValidIp' => $emailServiceConfig['assertValidIp'],
                    'validIpRanges' => $emailServiceConfig['validIpRanges'],
                    'prefix' => function ($message) use ($appEnv, $idpName) {
                        return Json::encode([
                            'app_env' => $appEnv,
                            'idp_name' => $idpName,
                        ]);
                    },
                ],
            ],
        ],
        
        'notifier' => $notifierConfig,
    ],
    'params' => [
        'syncSafetyCutoff' => Env::get('SYNC_SAFETY_CUTOFF'),
    ],
];
