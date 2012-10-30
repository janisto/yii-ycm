<?php
/* @var $this ModelController */
/* @var $title string */
/* @var $model object */
/* @var $data array */

$this->pageTitle=$title;
?>

<div class="btn-toolbar">
	<?php
	$this->widget('bootstrap.widgets.TbButtonGroup', array(
		'type'=>'',
		'buttons'=>array(
			array(
				'type'=>'primary',
				'label'=>Yii::t($this->module->translateCategory,'Create').' '.$this->module->getSingularName($model),
				'url'=>$this->createUrl('model/create',array('name'=>get_class($model))),
			),
		),
	));
	?>
</div>
<?php $this->widget('bootstrap.widgets.TbGridView',$data); ?>