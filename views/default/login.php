<?php
/* @var $this DefaultController */
/* @var $model LoginForm */
/* @var $form TbActiveForm  */

$this->pageTitle=$title;

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm',array(
    'id'=>'verticalForm',
	'type'=>'inline',
    'htmlOptions'=>array('class'=>'well'),
));
?>

<p><?php echo YcmModule::t('Please enter your password.'); ?></p>
<?php echo $form->passwordFieldRow($model,'password',array('class'=>'input-medium','prepend'=>'<i class="icon-lock"></i>')); ?>

<?php $this->widget('bootstrap.widgets.TbButton',array('buttonType'=>'submit','label'=>YcmModule::t('Login'))); ?>

<?php $this->endWidget(); ?>
