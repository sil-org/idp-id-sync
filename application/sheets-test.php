<?php
require __DIR__ . '/vendor/autoload.php';

use Sil\Idp\IdSync\common\components\adapters\GoogleSheetsIdStore;
use Sil\PhpEnv\Env;

$store = new GoogleSheetsIdStore();
$store->applicationName = Env::requireEnv('ID_STORE_applicationName');
$store->jsonAuthString = Env::requireEnv('ID_STORE_jsonAuthString');
$store->spreadsheetId = Env::requireEnv('ID_STORE_spreadsheetId');

var_dump($store->getAllActiveUsers());