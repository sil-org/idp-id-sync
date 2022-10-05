<?php

namespace Sil\Idp\IdSync\frontend\controllers;

use Sil\Idp\IdSync\common\traits\SyncProvider;
use Sil\Idp\IdSync\frontend\components\BaseRestController;
use yii\web\UnprocessableEntityHttpException;

class UserController extends BaseRestController
{
    use SyncProvider;

    public function actionChange($employeeId)
    {
        if (empty($employeeId)) {
            throw new UnprocessableEntityHttpException(
                'Employee ID cannot be empty',
                1491321384
            );
        }

        $synchronizer = $this->getSynchronizer();
        $synchronizer->syncUser($employeeId);
    }
}
