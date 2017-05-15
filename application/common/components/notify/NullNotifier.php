<?php
namespace Sil\Idp\IdSync\common\components\notify;

use Sil\Idp\IdSync\common\interfaces\NotifierInterface;
use Sil\Idp\IdSync\common\models\User;

/**
 * This Notifier can be used to avoid conditional log calls.
 */
class NullNotifier implements NotifierInterface
{
    public function sendMissingEmailNotice(User $user)
    {
        // noop
    }
}
