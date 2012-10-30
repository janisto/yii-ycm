<?php
/* @var $this DefaultController */
/* @var $model LoginForm */
/* @var $form TbActiveForm */

$this->pageTitle=$title;

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'verticalForm',
	'type'=>'inline',
	'htmlOptions'=>array('class'=>'well'),
));

echo '<p>'.Yii::t($this->module->translateCategory,'Please enter your username and password.').'</p>';
echo $form->textFieldRow($model,'username',array('class'=>'input-medium','prepend'=>'<i class="icon-user"></i>')).' ';
echo $form->passwordFieldRow($model,'password',array('class'=>'input-medium','prepend'=>'<i class="icon-lock"></i>')).' ';
$this->widget('bootstrap.widgets.TbButton',array('buttonType'=>'submit','label'=>Yii::t($this->module->translateCategory,'Login')));
echo '<br />'.$form->checkboxRow($model,'rememberMe');
$this->endWidget();