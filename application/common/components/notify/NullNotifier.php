<?php

namespace Sil\Idp\IdSync\common\components\notify;

use Sil\Idp\IdSync\common\interfaces\NotifierInterface;
use Sil\Idp\IdSync\common\models\User;

/**
 * This Notifier can be used to avoid conditional log calls.
 */
class NullNotifier implements NotifierInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSiteStatus(): string
    {
        return 'NullNotifier, so no status to check.';
    }

    public function sendMissingEmailNotice(array $users)
    {
        // noop
    }

    public function sendNewUserNotice(User $user)
    {
        // noop
    }
}
