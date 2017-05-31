<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

use Sil\Idp\IdSync\common\components\adapters\GoogleSheetsIdStore;
use Sil\PhpEnv\Env;

$store = new GoogleSheetsIdStore([
    'applicationName' => Env::requireEnv('ID_STORE_CONFIG_applicationName'),
    'jsonAuthString' => Env::requireEnv('ID_STORE_CONFIG_jsonAuthString'),
    'spreadsheetId' => Env::requireEnv('ID_STORE_CONFIG_spreadsheetId'),
]);

var_dump($store->getAllActiveUsers());