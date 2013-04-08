<?php
/* @var $this DefaultController */
/* @var $stats object */
/* @var $authUrl string */

$this->pageTitle=Yii::t('YcmModule.ycm','Setup');
$this->breadcrumbs=array(
	Yii::t('YcmModule.ycm','Setup'),
);

$cs=Yii::app()->clientScript;
$baseUrl=$this->module->assetsUrl;
$cs->registerScript('ycm-setup',"
function auth() {
	var D=640,A=480,C=screen.height,B=screen.width,H=Math.round((B/2)-(D/2)),G=0;
	if(C>A){G=Math.round((C/2)-(A/2))}
	authWin=window.open('". $authUrl ."','auth','left='+H+',top='+G+',width='+D+',height='+A+',personalbar=0,toolbar=0,scrollbars=1,resizable=1');
	if (window.focus) {authWin.focus()}
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
		<h4><?php echo Yii::t('YcmModule.ycm','Step 3'); ?></h4>
		<form method="post">
			<?php echo CHtml::dropDownList('profile',
				'',
				$data,
				array('empty'=>'(Select profile)')
			); ?><br>
			<button type="submit"><?php echo Yii::t('YcmModule.ycm','Next'); ?></button>
		</form>
<?php
	} else if (Yii::app()->request->getPost('profile',false)!==false) {
		echo '<h4>'.Yii::t('YcmModule.ycm','Add Tracking ID, Profile ID and Access Token to the main configuration file').'</h4>';
		$selected=Yii::app()->request->getPost('profile');
		$accessToken=Yii::app()->session->get('accessToken',false);
		$profiles=Yii::app()->session->get('profiles',false);
		echo "<strong>trackingId:</strong> ". $profiles[$selected]['trackingId'] ."<br />";
		echo "<strong>profileId:</strong> ". $profiles[$selected]['profileId'] ."<br />";
		echo "<strong>accessToken:</strong> $accessToken<br />";
	} else {
?>
	<div class="row-fluid">
		<h4><?php echo Yii::t('YcmModule.ycm','Step 1'); ?></h4>
		<a href="#" onclick="auth();"><?php echo Yii::t('YcmModule.ycm','Connect'); ?></a></p>
		<h4><?php echo Yii::t('YcmModule.ycm','Step 2'); ?></h4>
		<?php echo Yii::t('YcmModule.ycm','Paste Authorization code here:'); ?><br>
		<form method="post">
			<input type="text" name="code" id="code"><br>
			<button type="submit"><?php echo Yii::t('YcmModule.ycm','Next'); ?></button>
		</form>
	</div>
<?php } ?>