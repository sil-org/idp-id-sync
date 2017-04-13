<?php

use Sil\Idp\IdSync\common\components\IdBrokerBase;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\PhpEnv\Env;

return [
    'id' => 'id-sync',
    'components' => [
        'idBroker' => [
            'class' => IdBrokerBase::getAdapterClassFor(
                Env::get('ID_BROKER_ADAPTER')
            ),
            'accessToken' => Env::get('ID_BROKER_ACCESS_TOKEN'),
            'baseUrl' => Env::get('ID_BROKER_BASE_URL'),
        ],
        'idStore' => [
            'class' => IdStoreBase::getAdapterClassFor(
                Env::get('ID_STORE_ADAPTER')
            ),
            'apiKey' => Env::get('ID_STORE_API_KEY'),
            'apiSecret' => Env::get('ID_STORE_API_SECRET'),
            'baseUrl' => Env::get('ID_STORE_BASE_URL'),
        ],
        'log' => [
        ],
    ],
];
