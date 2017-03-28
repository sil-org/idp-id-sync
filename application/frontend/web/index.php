<?php

require(__DIR__ . '/../../vendor/autoload.php');

define('YII_ENV', Sil\PhpEnv\Env::get('APP_ENV', 'prod'));
define('YII_DEBUG', YII_ENV !== 'prod');

require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../config/main.php')
);

$application = new yii\web\Application($config);
$application->run();
