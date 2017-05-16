<?php
namespace Sil\Idp\IdSync\common\components\notify;

use Sil\Idp\IdSync\common\interfaces\NotifierInterface;
use Sil\Idp\IdSync\common\models\User;

/**
 * NOTE: If you add methods to this class, first add them to NotifierInterface.
 */
class ConsoleNotifier implements NotifierInterface
{
    public function sendMissingEmailNotice(User $user)
    {
        echo sprintf(
            'NOTIFIER: A user (%s, Employee ID %s) lacks an email address.',
            $user->username,
            $user->employeeId
        ) . PHP_EOL;
    }
}
