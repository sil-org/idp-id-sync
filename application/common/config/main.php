<?php

use Sil\Idp\IdSync\common\components\IdBrokerBase;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\components\notify\EmailServiceNotifier;
use Sil\Idp\IdSync\common\components\notify\NullNotifier;
use Sil\JsonLog\target\EmailServiceTarget;
use Sil\JsonLog\target\JsonStreamTarget;
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

$hrNotifierEmailTo = Env::get('NOTIFIER_EMAIL_TO');

$emailServiceConfig = Env::getArrayFromPrefix('EMAIL_SERVICE_');

// Re-retrieve the validIpRanges as an array.
$emailServiceConfig['validIpRanges'] = Env::getArray('EMAIL_SERVICE_validIpRanges');
if (empty($alertsEmail) && empty($hrNotifierEmailTo)) {
    $emailServiceConfig['baseUrl'] = 'x';
    $emailServiceConfig['accessToken'] = 'x';
    $emailServiceConfig['assertValidIp'] = false;
    $emailServiceConfig['validIpRanges'] = '';
}

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

$logPrefix = function () {
    $request = Yii::$app->request;
    $prefixData = [
        'env' => YII_ENV,
    ];
    if ($request instanceof \yii\web\Request) {
        // Assumes format: Bearer consumer-module-name-32randomcharacters
        $prefixData['id'] = substr($request->headers['Authorization'], 7, 16) ?: 'unknown';
        $prefixData['ip'] = $request->getUserIP();
        $prefixData['method'] = $request->getMethod();
        $prefixData['url'] = $request->getUrl();
    } elseif ($request instanceof \yii\console\Request) {
        $prefixData['id'] = '(console)';
    }

    return Json::encode($prefixData);
};

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
                    'class' => JsonStreamTarget::class,
                    'url' => 'php://stdout',
                    'levels' => ['info'],
                    'logVars' => [],
                    'categories' => ['application'],
                    'prefix' => $logPrefix,
                ],
                [
                    'class' => JsonStreamTarget::class,
                    'url' => 'php://stderr',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                    'prefix' => $logPrefix,
                ],
                [
                    'class' => EmailServiceTarget::class,
                    'categories' => ['application'],
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
                        'to' => $alertsEmail ?? '(disabled)',
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
        'allowEmptyEmail' => Env::get('ALLOW_EMPTY_EMAIL', false),
    ],
];
