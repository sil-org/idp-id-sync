<?php
namespace Sil\Idp\IdSync\common\interfaces;

use Sil\Idp\IdSync\common\models\User;

interface NotifierInterface
{
    /**
     * Send a notification that a User lacks an email address.
     *
     * @param User $user The User's available info.
     */
    public function sendMissingEmailNotice(User $user);
}
