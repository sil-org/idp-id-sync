<?php

require(__DIR__ . '/../../vendor/autoload.php');

define('YII_ENV', Sil\PhpEnv\Env::get('APP_ENV', 'prod'));
define('YII_DEBUG', YII_ENV === 'dev' || YII_ENV === 'test');

require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');

try {
    $config = yii\helpers\ArrayHelper::merge(
        require(__DIR__ . '/../../common/config/main.php'),
        require(__DIR__ . '/../config/main.php')
    );
} catch (\Exception $e) {

    // Return error response code/message to HTTP request.
    header('Content-Type: application/json');
    http_response_code(500);
    $responseContent = json_encode([
        'name' => 'Internal Server Error',
        'message' => $e->getMessage(),
        'status' => 500,
    ]);
    fwrite(fopen('php://stderr', 'w'), $responseContent . PHP_EOL);
    exit($responseContent);
}

$application = new yii\web\Application($config);
$application->run();
