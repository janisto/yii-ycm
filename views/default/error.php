<?php
/* @var $this DefaultController */
/* @var $code string */
/* @var $message string */

$this->pageTitle=Yii::app()->name.' - '.YcmModule::t('Error');
$this->breadcrumbs=array(
	YcmModule::t('Error'),
);
?>

<h2><?php echo YcmModule::t('Error').' '.$code; ?></h2>

<div class="error">
	<?php echo CHtml::encode($message); ?>
</div>