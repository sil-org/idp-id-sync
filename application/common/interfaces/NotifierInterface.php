<?php
namespace Sil\Idp\IdSync\common\interfaces;

use Sil\Idp\IdSync\common\models\User;

interface NotifierInterface
{
    /**
     * Send a notification that there are Users that lack an email address.
     *
     * @param User[] $users The list of Users.
     */
    public function sendMissingEmailNotice(array $users);
}
