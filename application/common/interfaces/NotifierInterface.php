<?php
namespace Sil\Idp\IdSync\common\interfaces;

use Sil\Idp\IdSync\common\models\User;

interface NotifierInterface
{
    /**
     * If using a notifier that depends on an external service (such as the
     * Email Service), check that service's status URL. If there is a problem,
     * an exception will be thrown.
     *
     * If no external service is involved, or if that service's status check
     * comes back fine, no exception will be thrown and a string will be
     * returned.
     *
     * @return string
     * @throws Exception
     */
    public function getSiteStatus();
    
    /**
     * Send a notification that there are Users that lack an email address.
     *
     * @param User[] $users The list of Users.
     */
    public function sendMissingEmailNotice(array $users);
}
