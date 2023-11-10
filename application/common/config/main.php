<?php

use notamedia\sentry\SentryTarget;
use Sentry\Event;
use Sil\Idp\IdSync\common\components\IdBrokerBase;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\components\notify\EmailServiceNotifier;
use Sil\JsonLog\target\EmailServiceTarget;
use Sil\JsonLog\target\JsonStreamTarget;
use Sil\PhpEnv\Env;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

$alertsEmail = Env::get('ALERTS_EMAIL');
$idpName = Env::requireEnv('IDP_NAME');
$idpDisplayName = Env::get('IDP_DISPLAY_NAME', $idpName);

$idBrokerOptionalConfig = Env::getArrayFromPrefix('ID_BROKER_CONFIG_');
$idBrokerOptionalConfig['trustedIpRanges'] = Env::getArray('ID_BROKER_CONFIG_trustedIpRanges');
$idStoreOptionalConfig = Env::getArrayFromPrefix('ID_STORE_CONFIG_');

$hrNotifierEmailTo = Env::get('NOTIFIER_EMAIL_TO');

$emailServiceConfig = Env::getArrayFromPrefix('EMAIL_SERVICE_');

// Re-retrieve the validIpRanges as an array.
$emailServiceConfig['validIpRanges'] = Env::getArray('EMAIL_SERVICE_validIpRanges');

/* Configure the notifier, used to send notifications to HR (such as
 * when users lack an email address):  */
$notifierConfig = [
    'class' => EmailServiceNotifier::class,
    'emailServiceConfig' => $emailServiceConfig,
    'emailTo' => $hrNotifierEmailTo,
    'organizationName' => $idpDisplayName,
];

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
                    'exportInterval' => 1,
                ],
                [
                    'class' => JsonStreamTarget::class,
                    'url' => 'php://stderr',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                    'prefix' => $logPrefix,
                    'exportInterval' => 1,
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
                        'yii\web\HttpException:502',
                        'Sil\EmailService\Client\EmailServiceClientException',
                    ],
                    'levels' => ['error'],
                    'logVars' => [], // Disable logging of _SERVER, _POST, etc.
                    'message' => [
                        'to' => $alertsEmail ?? '(disabled)',
                        'subject' => 'ERROR - ' . $idpName . ' ID Sync [' . YII_ENV .']',
                    ],
                    'baseUrl' => $emailServiceConfig['baseUrl'],
                    'accessToken' => $emailServiceConfig['accessToken'],
                    'assertValidIp' => $emailServiceConfig['assertValidIp'],
                    'validIpRanges' => $emailServiceConfig['validIpRanges'],
                    'prefix' => function ($message) use ($idpName) {
                        return Json::encode([
                            'app_env' => YII_ENV,
                            'idp_name' => $idpName,
                        ]);
                    },
                    'exportInterval' => 1,
                ],
                [
                    'class' => SentryTarget::class,
                    'enabled' => !empty(Env::get('SENTRY_DSN')),
                    'dsn' => Env::get('SENTRY_DSN'),
                    'levels' => ['error'],
                    'context' => true,
                    // Additional options for `Sentry\init`
                    // https://docs.sentry.io/platforms/php/configuration/options
                    'clientOptions' => [
                        'attach_stacktrace' => false, // stack trace identifies the logger call stack, not helpful
                        'environment' => YII_ENV,
                        'release' => 'idp-id-sync@4.5.1-pre',
                        'before_send' => function (Event $event) use ($idpName): ?Event {
                            $event->setExtra(['idp' => $idpName]);
                            return $event;
                        },
                    ],
                ],
            ],
        ],

        'notifier' => $notifierConfig,
    ],
    'params' => [
        'syncSafetyCutoff' => Env::get('SYNC_SAFETY_CUTOFF'),
        'allowEmptyEmail' => Env::get('ALLOW_EMPTY_EMAIL', false),
        'enableNewUserNotification' => Env::get('ENABLE_NEW_USER_NOTIFICATION', false),
        'sentryMonitorSlug' => Env::get('SENTRY_MONITOR_SLUG', 'idp-id-sync'),
    ],
];
