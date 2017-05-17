<?php
use yii\helpers\Html;

/* @var $this \yii\web\View The view component instance. */
/* @var $message \yii\mail\BaseMessage Newly created mail message. */
/* @var $idStoreName string */
/* @var $organizationName string */
/* @var $users User[] */

?>
<h2>Missing Email</h2>
<p>
  The following <?= count($users) ?> user(s) do not have an email 
  address. Without this, they will be unable to log in to certain 
  <?= Html::encode($organizationName) ?> websites.
</p>
<ol>
  <?php foreach ($users as $user): ?>
    <li><?= Html::encode($username) ?>, <?= Html::encode($employeeId) ?></li>
  <?php endforeach; ?>
</ol>
