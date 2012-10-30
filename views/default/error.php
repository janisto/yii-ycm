<?php
/* @var $this DefaultController */
/* @var $code string */
/* @var $message string */

$this->pageTitle=Yii::app()->name.' - '.Yii::t($this->module->translateCategory,'Error');
$this->breadcrumbs=array(
	Yii::t($this->module->translateCategory,'Error'),
);
?>

<h2><?php echo Yii::t($this->module->translateCategory,'Error').' '.$code; ?></h2>

<div class="alert alert-error">
	<?php echo CHtml::encode($message); ?>
</div>