<?php
use yii\helpers\Html;

/* @var $this \yii\web\View The view component instance. */
/* @var $message \yii\mail\BaseMessage Newly created mail message. */
/* @var $idStoreName string */
/* @var $organizationName string */
/* @var $employeeId string */
/* @var $username string */
/* @var $firstName string */
/* @var $lastName string */

?>
<h2>Missing Email</h2>
<p>
  The following <?= Html::encode($idStoreName) ?> user is missing an email 
  address. Without this, they will be unable to log in to certain 
  <?= Html::encode($organizationName) ?> websites.
</p>
<ul>
  <li><b>Employee ID:</b> <?= Html::encode($employeeId) ?></li>
  <li><b>Username:</b> <?= Html::encode($username) ?></li>
  <li><b>First Name:</b> <?= Html::encode($firstName) ?></li>
  <li><b>Last Name:</b> <?= Html::encode($lastName) ?></li>
</ul>
