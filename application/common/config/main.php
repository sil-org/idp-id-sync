<?php

use Sil\Idp\IdSync\common\components\IdBrokerBase;
use Sil\Idp\IdSync\common\components\IdStoreBase;
use Sil\PhpEnv\Env;
use yii\helpers\ArrayHelper;

$idBrokerOptionalConfig = [];
if (Env::get('ID_BROKER_ACCESS_TOKEN') !== null) {
    $idBrokerOptionalConfig['accessToken'] = Env::get('ID_BROKER_ACCESS_TOKEN');
}
if (Env::get('ID_BROKER_BASE_URL') !== null) {
    $idBrokerOptionalConfig['baseUrl'] = Env::get('ID_BROKER_BASE_URL');
}

$idStoreOptionalConfig = [];
if (Env::get('ID_STORE_API_KEY') !== null) {
    $idStoreOptionalConfig['apiKey'] = Env::get('ID_STORE_API_KEY');
}
if (Env::get('ID_STORE_API_SECRET') !== null) {
    $idStoreOptionalConfig['apiSecret'] = Env::get('ID_STORE_API_SECRET');
}
if (Env::get('ID_STORE_BASE_URL') !== null) {
    $idStoreOptionalConfig['baseUrl'] = Env::get('ID_STORE_BASE_URL');
}

return [
    'id' => 'id-sync',
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
        ],
    ],
];
