<?php

use Sil\Idp\IdSync\common\models\User;
use yii\helpers\Html;

/* @var $organizationName string */
/* @var $user User */

?>
<p>
  <?= Html::encode($user->getHRContactName()) ?>,
</p>

<p>
  The <?= Html::encode($organizationName) ?> IDP account you requested for
    <?php if (empty($user->getDisplayName())) {
        echo Html::encode($user->getFirstName() . ' ' . $user->getLastName());
    } else {
        echo Html::encode($user->getDisplayName());
    }?>
    (Staff ID <?= Html::encode($user->getEmployeeId()) ?>) has been created.
    Their username is <?= Html::encode($user->getUsername()) ?>.
</p>

<p>
  An invite message will be sent to <?= Html::encode($user->getFirstName()) ?> at

    <?php if (empty($user->getEmail())) {
        echo Html::encode($user->getPersonalEmail());
    } elseif (empty($user->getPersonalEmail())) {
        echo Html::encode($user->getEmail());
    } else {
        echo Html::encode($user->getEmail()) . ' and ' . Html::encode($user->getPersonalEmail());
    }?>
</p>

<hr>

This is an automated message.
