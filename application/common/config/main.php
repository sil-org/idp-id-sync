<?php

use Sil\Idp\IdSync\common\components\IdBrokerBase;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\Idp\IdSync\common\components\notify\EmailServiceNotifier;
use Sil\Idp\IdSync\common\components\notify\NullNotifier;
use Sil\JsonSyslog\JsonSyslogTarget;
use Sil\PhpEnv\Env;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\swiftmailer\Mailer;

$appEnv = Env::get('APP_ENV', 'prod'); // Have default match "application/frontend/web/index.php".
$idpName = Env::requireEnv('IDP_NAME');
$idpDisplayName = Env::get('IDP_DISPLAY_NAME', $idpName);

$idBrokerOptionalConfig = Env::getArrayFromPrefix('ID_BROKER_CONFIG_');
$idStoreOptionalConfig = Env::getArrayFromPrefix('ID_STORE_CONFIG_');

$emailServiceConfig = Env::getArrayFromPrefix('EMAIL_SERVICE_');

// Re-retrieve the validIpRanges as an array.
$emailServiceConfig['validIpRanges'] = Env::getArray('EMAIL_SERVICE_validIpRanges');

$notifierEmailTo = Env::get('NOTIFIER_EMAIL_TO');
if (empty($notifierEmailTo)) {
    $notifierConfig = ['class' => NullNotifier::class];
} else {
    /* Configure the notifier, used to send notifications to HR (such as
     * when users lack an email address):  */
    $notifierConfig = [
        'class' => EmailServiceNotifier::class,
        'emailServiceConfig' => $emailServiceConfig,
        'emailTo' => $notifierEmailTo,
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
                    
                    'prefix' => function($message) use ($appEnv, $idpName) {
                        return Json::encode([
                            'app_env' => $appEnv,
                            'idp_name' => $idpName,
                        ]);
                    },
                ],
            ],
        ],
        
        'mailer' => [
            'class' => Mailer::class,
            'htmlLayout' => '@common/mail/layouts/html.php',
            'useFileTransport' => Env::get('MAILER_USEFILES', false),
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => Env::get('MAILER_HOST'),
                'username' => Env::get('MAILER_USERNAME'),
                'password' => Env::get('MAILER_PASSWORD'),
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ],
        
        'notifier' => $notifierConfig,
    ],
    'params' => [
        'syncSafetyCutoff' => Env::get('SYNC_SAFETY_CUTOFF'),
    ],
];
