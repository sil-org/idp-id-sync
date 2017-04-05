<?php
namespace Sil\Idp\IdSync\frontend\controllers;

use Sil\Idp\IdSync\common\sync\Synchronizer;
use Sil\Idp\IdSync\frontend\components\BaseRestController;
use Yii;
use yii\web\UnprocessableEntityHttpException;

class UserController extends BaseRestController
{
    public function actionChange($employeeId)
    {
        if (empty($employeeId)) {
            throw new UnprocessableEntityHttpException(
                'Employee ID cannot be empty',
                1491321384
            );
        }
        
        $synchronizer = new Synchronizer(Yii::$app->idStore, Yii::$app->idBroker);
        $synchronizer->syncUser($employeeId);
    }
}
