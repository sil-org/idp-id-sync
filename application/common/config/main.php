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

$idBrokerOptionalConfig = Env::getArrayFromPrefix('ID_BROKER_CONFIG_');
$idStoreOptionalConfig = Env::getArrayFromPrefix('ID_STORE_CONFIG_');

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
             * To send notifications emails (such as to HR when a user lacks an
             * email address), provide both a 'to' and a 'from' email address.
             */
            'emailTo' => Env::get('NOTIFIER_EMAIL_TO'),
            'emailFrom' => Env::get('MAILER_USERNAME'),
            'organizationName' => $idpName,
        ],
        'syncSafetyCutoff' => Env::get('SYNC_SAFETY_CUTOFF'),
    ],
];
