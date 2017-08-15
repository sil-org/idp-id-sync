<?php

/* @var $organizationName string */
/* @var $users User[] */

$numUsers = count($users);
?>
Missing Email
-------------

The following <?= ($numUsers === 1 ? 'user does' : $numUsers . ' users do') ?> 
not have an email address. Without this, they will be unable to log in to 
certain <?= $organizationName ?> websites.

<?php
$counter = 0;
foreach ($users as $user) {
    echo sprintf('%s. Employee ID %s', ++$counter, $user->employeeId);
    if ($user->username !== null) {
        echo sprintf(' (%s)', $user->username);
    }
    echo "\n";
}        
