<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

use Sil\Idp\IdSync\common\components\adapters\GoogleSheetsIdStore;
use Sil\PhpEnv\Env;

$store = new GoogleSheetsIdStore(Env::getArrayFromPrefix('ID_STORE_CONFIG_'));

var_dump($store->getAllActiveUsers());
