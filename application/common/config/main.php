<?php

use Sil\Idp\IdSync\common\components\IdBrokerBase;
use Sil\Idp\IdSync\common\components\IdStoreBase;
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
    ],
    'params' => [
        'notifier' => [
            /*
             * To send notifications emails (such as to HR when users lack an
             * email address), provide the following fields.
             */
            'emailServiceConfig' => $emailServiceConfig,
            'emailTo' => Env::get('NOTIFIER_EMAIL_TO'),
            'organizationName' => $idpDisplayName,
        ],
        'syncSafetyCutoff' => Env::get('SYNC_SAFETY_CUTOFF'),
    ],
];
