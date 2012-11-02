<?php
/* @var $this DefaultController */
/* @var $models array */

$this->pageTitle=$title;
?>

<?php $this->beginWidget('bootstrap.widgets.TbHeroUnit'); ?>
	<?php foreach($models as $model): ?>
		<div class="btn-toolbar">
			<?php
			$download=false;
			$downloadItems=array();
			if ($this->module->getDownloadExcel($model)) {
				$download=true;
				array_push($downloadItems,array(
					'label'=>Yii::t('YcmModule.ycm','Excel'),
					'url'=>$this->createUrl('model/excel',array('name'=>$model)),
				));
			}
			if ($this->module->getDownloadMsCsv($model)) {
				$download=true;
				array_push($downloadItems,array(
					'label'=>Yii::t('YcmModule.ycm','MS CSV'),
					'url'=>$this->createUrl('model/mscsv',array('name'=>$model)),
				));
			}
			if ($this->module->getDownloadCsv($model)) {
				$download=true;
				array_push($downloadItems,array(
					'label'=>Yii::t('YcmModule.ycm','CSV'),
					'url'=>$this->createUrl('model/csv',array('name'=>$model)),
				));
			}
			$this->widget('bootstrap.widgets.TbButtonGroup', array(
				'type'=>'', // '', 'primary', 'info', 'success', 'warning', 'danger' or 'inverse'
				'buttons'=>array(
					array(
						'type'=>'primary',
						'label'=>$this->module->getAdminName($model),
						'url'=>$this->createUrl('model/list',array('name'=>$model)),
					),
					array(
						'label'=>Yii::t('YcmModule.ycm','Create'),
						'url'=>$this->createUrl('model/create',array('name'=>$model)),
					),
					array(
						'label'=>Yii::t('YcmModule.ycm','List'),
						'url'=>$this->createUrl('model/list',array('name'=>$model)),
					),
				),
			));
			if ($download) {
				$this->widget('bootstrap.widgets.TbButtonGroup', array(
					'type'=>'',
					'buttons'=>array(
						array('label'=>Yii::t('YcmModule.ycm',
							'Download {name}',
							array('{name}'=>$this->module->getPluralName($model))
						)),
						array('items'=>$downloadItems),
					),
				));
			}
			?>
		</div>
	<?php endforeach; ?>
<?php $this->endWidget(); ?>
