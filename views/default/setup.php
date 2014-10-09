<?php
/* @var $this DefaultController */
/* @var $stats Stats */

$this->pageTitle=Yii::t('YcmModule.ycm','Google Analytics setup');
$this->breadcrumbs=array(
	Yii::t('YcmModule.ycm','Google Analytics setup'),
);

$authUrl=$stats->client->createAuthUrl();

$cs=Yii::app()->clientScript;
$baseUrl=$this->module->assetsUrl;
$cs->registerScript('ycm-setup',"
function auth() {
	var D=640,A=480,C=screen.height,B=screen.width,H=Math.round((B/2)-(D/2)),G=0;
	if (C>A) { G=Math.round((C/2)-(A/2)) }
	authWin=window.open('". $authUrl ."','auth','left='+H+',top='+G+',width='+D+',height='+A+',personalbar=0,toolbar=0,scrollbars=1,resizable=1');
	if (window.focus) { authWin.focus() }
	return false;
}
", CClientScript::POS_END);

Yii::app()->session->open();
?>

<h2><?php echo Yii::t('YcmModule.ycm','Google Analytics setup'); ?></h2>

<?php
	if ($authCode=Yii::app()->request->getPost('code',false)) {
		$accessToken=$stats->authenticate($authCode);
		Yii::app()->session->add('accessToken',$accessToken);

		$profiles=$stats->allProfiles;
		Yii::app()->session->add('profiles',$profiles);

		$data=array();
		foreach ($profiles as $key=>$profile) {
			$data[$key]=$profile['name'];
		}
?>
		<h4><?php echo Yii::t('YcmModule.ycm','Step {num}',array('{num}'=>3)); ?></h4>
		<?php
		$form=$this->beginWidget('CActiveForm', array(
				'enableAjaxValidation'=>false,
				'htmlOptions'=>array(
					'class'=>'form-horizontal'
				),
			));
		?>
			<p><?php echo Yii::t('YcmModule.ycm','Select profile.'); ?></p>
			<p><?php echo CHtml::dropDownList('profile','',$data); ?></p>
			<p><button type="submit" class="btn"><?php echo Yii::t('YcmModule.ycm','Next'); ?></button></p>
		<?php $this->endWidget(); ?>
<?php
	} else if (Yii::app()->request->getPost('profile',false)!==false) {
		$selected=Yii::app()->request->getPost('profile');
		$accessToken=Yii::app()->session->get('accessToken',false);
		$profiles=Yii::app()->session->get('profiles',false);
?>
		<h4><?php echo Yii::t('YcmModule.ycm','Step {num}',array('{num}'=>4)); ?></h4>
		<p><?php echo Yii::t('YcmModule.ycm','Add Tracking ID, Profile ID and Access Token to the main configuration file.'); ?></p>
		<pre>
...
'ycm'=>array(
	...
	'analytics'=>array(
		'trackingId'=>'<?php echo $profiles[$selected]['trackingId']; ?>',
		'profileId'=><?php echo $profiles[$selected]['profileId']; ?>,
		'accessToken'=>'<?php echo $accessToken; ?>',
	),
),
...
		</pre>
		<?php
		$form=$this->beginWidget('CActiveForm', array(
				'enableAjaxValidation'=>false,
				'htmlOptions'=>array(
					'class'=>'form-horizontal'
				),
			));
		?>
			<div class="control-group">
				<label class="control-label" for="trackingId">trackingId</label>
				<div class="controls">
					<input class="span5" type="text" id="trackingId" value="<?php echo $profiles[$selected]['trackingId']; ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="profileId">profileId</label>
				<div class="controls">
					<input class="span5" type="text" id="profileId" value="<?php echo $profiles[$selected]['profileId']; ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="accessToken">accessToken</label>
				<div class="controls">
					<input class="span5" type="text" id="accessToken" value='<?php echo $accessToken; ?>' />
				</div>
			</div>
		<?php $this->endWidget(); ?>
		<h4><?php echo Yii::t('YcmModule.ycm','Step {num}',array('{num}'=>5)); ?></h4>
		<p><?php echo CHtml::link(Yii::t('YcmModule.ycm','Reload page'),array('/'.$this->module->name.'/default/stats'),array('class'=>'btn btn-primary')); ?></p>
<?php
	} else {
?>
	<div class="row-fluid">
		<h4><?php echo Yii::t('YcmModule.ycm','Step {num}',array('{num}'=>1)); ?></h4>
		<p><?php echo Yii::t('YcmModule.ycm','Connect with your Google Analytics account.'); ?></p>
		<p><a class="btn btn-primary" href="#" onclick="auth();"><?php echo Yii::t('YcmModule.ycm','Connect'); ?></a></p>
		<h4><?php echo Yii::t('YcmModule.ycm','Step {num}',array('{num}'=>2)); ?></h4>
		<?php
		$form=$this->beginWidget('CActiveForm', array(
				'enableAjaxValidation'=>false,
				'htmlOptions'=>array(
					'class'=>'form-horizontal'
				),
			));
		?>
			<label for="code"><?php echo Yii::t('YcmModule.ycm','Paste authorization code here:'); ?></label>
			<p><input class="span5" type="text" name="code" id="code"></p>
			<p><button type="submit" class="btn"><?php echo Yii::t('YcmModule.ycm','Next'); ?></button></p>
		<?php $this->endWidget(); ?>
	</div>
<?php } ?>