<?php

use Sil\Idp\IdSync\common\models\User;

/* @var $organizationName string */
/* @var $user User */

?>
    New User
    --------

    The following user has just been activated in the <?= $organizationName ?> IdP:

<?php
    echo sprintf('Employee ID %s', $user->getEmployeeId());
    if ($user->getUsername() !== null) {
        echo sprintf(' (%s)', $user->getUsername());
    }
    echo "\n";
