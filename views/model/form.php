<?php
/* @var $this ModelController */
/* @var $title string */
/* @var $model object */
/* @var $form TbActiveForm */

$this->pageTitle=$title;

$attributes=array();
foreach ($model->attributeLabels() as $attribute=>$label) {
	if (isset($model->tableSchema->columns[$attribute]) && $model->tableSchema->columns[$attribute]->isPrimaryKey===true) {
		continue;
	}
	$attributes[]=$attribute;
}
$attributes=array_filter(array_unique(array_map('trim',$attributes)));
?>

<div class="row-fluid">
	<div class="span10">
		<?php
		$form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
			'id'=>get_class($model).'-id-form',
			'type'=>'horizontal',
			'inlineErrors'=>false,
			'htmlOptions'=>array('enctype'=>'multipart/form-data'),
		));
		echo $form->errorSummary($model);
		foreach ($attributes as $attribute) {
			$this->module->createWidget($form,$model,$attribute);
		}
		?>
		<div class="form-actions">
			<?php
			$buttons=array(
				array(
					'buttonType'=>'submit',
					'type'=>'primary',
					'label'=>Yii::t('YcmModule.ycm','Save'),
					'htmlOptions'=>array('name'=>'_save')
				),
				array(
					'buttonType'=>'submit',
					'label'=>Yii::t('YcmModule.ycm','Save and add another'),
					'htmlOptions'=>array('name'=>'_addanother')
				),
				array(
					'buttonType'=>'submit',
					'label'=>Yii::t('YcmModule.ycm','Save and continue editing'),
					'htmlOptions'=>array('name'=>'_continue')
				),
			);
			if (!$model->isNewRecord) {
				array_push($buttons,array(
					'buttonType'=>'link',
					'type'=>'danger',
					'url'=>'#',
					'label'=>Yii::t('YcmModule.ycm','Delete'),
					'htmlOptions'=>array(
						'submit'=>array(
							'model/delete',
							'name'=>get_class($model),
							'pk'=>$model->primaryKey,
						),
						'confirm'=>Yii::t('YcmModule.ycm','Are you sure you want to delete this item?'),
					)
				));
			}
			$this->widget('bootstrap.widgets.TbButtonGroup',array(
				'type'=>'',
				'buttons'=>$buttons,
			));
			?>
		</div>
		<?php $this->endWidget(); ?>
	</div>
</div>