<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

use Sil\Idp\IdSync\common\components\adapters\GoogleSheetsIdStore;
use Sil\PhpEnv\Env;

$store = new GoogleSheetsIdStore([
    'applicationName' => Env::requireEnv('ID_STORE_applicationName'),
    'jsonAuthString' => Env::requireEnv('ID_STORE_jsonAuthString'),
    'spreadsheetId' => Env::requireEnv('ID_STORE_spreadsheetId'),
]);

var_dump($store->getAllActiveUsers());