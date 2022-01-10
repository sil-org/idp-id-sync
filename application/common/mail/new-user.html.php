<?php
use Sil\Idp\IdSync\common\models\User;
use yii\helpers\Html;

/* @var $organizationName string */
/* @var $user User */

?>
<h2>New 'User'</h2>
<p>
  The following user has just been created in the <?= Html::encode($organizationName) ?> IdP:
</p>
<ol>
    <li>
        Employee ID <?= Html::encode($user->getEmployeeId()) ?>
        <?php if ($user->getUsername() !== null): ?>
            (<?= Html::encode($user->getUsername()) ?>)
        <?php endif; ?>
    </li>
</ol>
