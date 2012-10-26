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
			array('label'=>YcmModule::t('Create').' '.$this->module->getSingularName($model),'url'=>$this->createUrl('model/create',array('name'=>get_class($model))),'type'=>'primary'),
		),
	));
	?>
</div>
<?php $this->widget('bootstrap.widgets.TbGridView',$data); ?>