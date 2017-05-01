<?php
namespace Sil\Idp\IdSync\console\controllers;

use yii\console\Controller;

class BatchController extends Controller
{
    public function actionFull()
    {
        // TEMP
        echo "Full\n";
    }
    
    public function actionIncremental()
    {
        // TEMP
        echo "Incremental\n";
        throw new \Exception('Testing error output.');
    }
}
