<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'My Yii Application';
?>

<div class="site-about">
    <h2>Hello, <?= Yii::$app->user->identity->username ?>!</h2>

    <?
    echo Html::beginForm(['/site/logout'], 'post');
    echo Html::submitButton(
        'Logout',
        ['class' => 'btn btn-default logout']
    );
    echo Html::endForm();
    ?>
</div>
