<?php
/* @var $this AdminController */

$cs=Yii::app()->clientScript;
$baseUrl=$this->module->assetsUrl;
$cs->registerCoreScript('jquery');
$cs->registerCssFile($baseUrl.'/css/styles.css');
?>
<!DOCTYPE html>
<html lang="<?php echo Yii::app()->language; ?>">
<head>
	<meta charset="utf-8">
	<meta name="robots" content="NONE,NOARCHIVE" />
	<title><?php print YcmModule::t('Administration') ?></title>
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
<?php
$this->widget('bootstrap.widgets.TbNavbar', array(
	'type'=>'inverse', // null or 'inverse'
    'brand'=>YcmModule::t('Administration'),
	'brandUrl'=>Yii::app()->createUrl('/'.$this->module->name),
    'collapse'=>true, // requires bootstrap-responsive.css
    'items'=>array(
        array(
            'class'=>'bootstrap.widgets.TbMenu',
            'htmlOptions'=>array('class'=>'pull-right'),
            'items'=>array(
        		array('label'=>YcmModule::t('Login'),'url'=>array('/'.$this->module->name.'/default/login'),'visible'=>Yii::app()->user->isGuest),
        		array('label'=>YcmModule::t('Logout'),'url'=>array('/'.$this->module->name.'/default/logout'),'visible'=>!Yii::app()->user->isGuest)
            ),
        ),
    ),
));
?>

<?php if (!empty($this->breadcrumbs)):?>
<div class="container-fluid">
	<?php $this->widget('bootstrap.widgets.TbBreadcrumbs',array(
		'links'=>$this->breadcrumbs,
		'separator'=>'/',
		'homeLink'=>CHtml::link(YcmModule::t('Home'),Yii::app()->createUrl('/'.$this->module->name)),
	)); ?>
</div>
<?php endif?>

<div class="container-fluid">
	<?php $this->widget('bootstrap.widgets.TbAlert', array(
		'block'=>true, // display a larger alert block?
		'fade'=>true, // use transitions?
		'closeText'=>'&times;', // close link text - if set to false, no close link is displayed
	)); ?>

	<?php echo $content; ?>

</div>
</body>
</html>