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
        ],
    ],
];
