<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Upload File';
?>

<h2 class="title"><?php echo $this->title; ?></h2><span class="line"></span>
<div class="clearfix"></div>
<div class="content">
    <div class="container">

        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'],'layout' => 'horizontal']) ?>

        <?= $form->field($model, 'file')->fileInput() ?>

        <div class="col-md-12 text-center"><button class="btn btn-primary" type="submit">Submit</button></div>

        <?php ActiveForm::end() ?>
    </div>
</div>
