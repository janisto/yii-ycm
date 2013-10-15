<?php
/* @var $this DefaultController */
/* @var $days integer */
/* @var $deviceData array */
/* @var $visitorData array */
/* @var $trafficData array */
/* @var $keywords array */
/* @var $referrers array */
/* @var $pages object */
/* @var $usage array */

$this->pageTitle=Yii::t('YcmModule.ycm','Statistics');
$this->breadcrumbs=array(
	Yii::t('YcmModule.ycm','Statistics'),
);

$lang=str_replace('-','_',strtolower(Yii::app()->language));
$parts=explode('_',$lang);
if (count($parts)==2) {
	$lang=$parts[0].'-'.strtoupper($parts[1]);
}

$cs=Yii::app()->clientScript;
$baseUrl=$this->module->assetsUrl;
$cs->registerCssFile($baseUrl.'/css/morris.min.css');
$cs->registerScriptFile($baseUrl.'/js/raphael.min.js',CClientScript::POS_END);
$cs->registerScriptFile($baseUrl.'/js/morris.min.js',CClientScript::POS_END);
$cs->registerCoreScript('jquery');
$cs->registerCoreScript('jquery.ui');
if ($lang!='en' && $lang!='en-US') {
	$cs->registerScriptFile($cs->getCoreScriptUrl().'/jui/js/jquery-ui-i18n.min.js');
	$cs->registerScript('ycm-localize-date-'.$lang,"
	jQuery(function($) {
		var lang='$lang';
		if (jQuery.datepicker.regional[lang]===undefined) {
			lang=''; // back to default language
		}
		jQuery.datepicker.setDefaults(jQuery.datepicker.regional[lang]);
	});
	", CClientScript::POS_END);
}
$cs->registerScript('ycm-morris',"
jQuery(function($) {
	formatNumber = function(num) {
		var absnum, intnum, ret, strabsnum;
		if (num != null) {
			ret = num < 0 ? '-' : '';
			absnum = Math.abs(num);
			intnum = Math.floor(absnum).toFixed(0);
			ret += intnum.replace(/(?=(?:\d{3})+$)(?!^)/g, '".Yii::app()->locale->getNumberSymbol('group')."');
			strabsnum = absnum.toString();
			if (strabsnum.length > intnum.length) {
				ret += strabsnum.slice(intnum.length);
			}
			return ret;
		} else {
			return '-';
		}
	};
	Morris.Line({
		element: 'visitorData',
		hideHover: 'auto',
		lineColors: ['#1f77b4','#ff7f0e','#2ca02c','#d62728','#9467bd','#8c564b','#e377c2','#7f7f7f','#bcbd22','#17becf'],
		xLabelFormat: function (x) { return $.datepicker.formatDate('M d', new Date(x)); },
		yLabelFormat: function (y) { return formatNumber(y); },
		dateFormat: function (x) { return $.datepicker.formatDate('DD, MM d, yy', new Date(x)); },
		data: ".CJSON::encode($visitorData).",
		xkey: 'date',
		ykeys: ['a','b','c','d','e'],
		labels: [
			'".Yii::t('YcmModule.ycm','Pageviews')."',
			'".Yii::t('YcmModule.ycm','Unique Pageviews')."',
			'".Yii::t('YcmModule.ycm','Visits')."',
			'".Yii::t('YcmModule.ycm','Unique Visitors')."',
			'".Yii::t('YcmModule.ycm','New Visitors')."'
		]
	});
	Morris.Area({
		element: 'platformData',
		hideHover: 'auto',
		lineColors: ['#1f77b4','#ff7f0e','#2ca02c','#d62728','#9467bd','#8c564b','#e377c2','#7f7f7f','#bcbd22','#17becf'],
		xLabelFormat: function (x) { return $.datepicker.formatDate('M d', new Date(x)); },
		yLabelFormat: function (y) { return formatNumber(y); },
		dateFormat: function (x) { return $.datepicker.formatDate('DD, MM d, yy', new Date(x)); },
		data: ".CJSON::encode($deviceData).",
		xkey: 'date',
		ykeys: ['a','b','c'],
		labels: [
			'".Yii::t('YcmModule.ycm','Desktop Pageviews')."',
			'".Yii::t('YcmModule.ycm','Tablet Pageviews')."',
			'".Yii::t('YcmModule.ycm','Smartphone Pageviews')."'
		]
	});
	Morris.Donut({
		element: 'trafficData',
		colors: ['#1f77b4','#ff7f0e','#2ca02c','#d62728','#9467bd','#8c564b','#e377c2','#7f7f7f','#bcbd22','#17becf'],
		formatter: function (y,data) { return '".Yii::t('YcmModule.ycm','Visits').": '+formatNumber(y); },
		data: ".CJSON::encode($trafficData)."
	});
});
", CClientScript::POS_END);
?>

<h2><?php echo Yii::t('YcmModule.ycm','Google Analytics summary for the past {days} days',array('{days}'=>$days)); ?></h2>

<div class="row-fluid">
	<h3><?php echo Yii::t('YcmModule.ycm','Overview'); ?></h3>
	<div id="visitorData"></div>
</div>

<div class="row-fluid">
	<h3><?php echo Yii::t('YcmModule.ycm','Device Traffic'); ?></h3>
	<div id="platformData"></div>
</div>

<div class="row-fluid">
	<div class="span9">
		<h3><?php echo Yii::t('YcmModule.ycm','Top Pages'); ?></h3>
		<?php if (count($pages->rows)>0): ?>
			<table class="table table-striped">
				<thead>
				<tr>
					<th>#</th>
					<th><?php echo Yii::t('YcmModule.ycm','Page'); ?></th>
					<th><?php echo Yii::t('YcmModule.ycm','Pageviews'); ?></th>
					<th><?php echo Yii::t('YcmModule.ycm','Unique Pageviews'); ?></th>
					<th><?php echo Yii::t('YcmModule.ycm','Avg. Time on Page'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				$i=0;
				$total1=$pages->totalsForAllResults['ga:pageviews'];
				$total2=$pages->totalsForAllResults['ga:uniquePageviews'];
				foreach ($pages->rows as $item) {
					$percentage1=Yii::app()->numberFormatter->formatPercentage($item[3]/$total1);
					$percentage2=Yii::app()->numberFormatter->formatPercentage($item[4]/$total2);
					$value1=Yii::app()->numberFormatter->formatDecimal($item[3]);
					$value2=Yii::app()->numberFormatter->formatDecimal($item[4]);
					$hostname=$item[0];
					$path=$item[1];
					$time=gmdate('H:i:s',$item[5]);
					$url='http://'.$hostname.$path;
					if (strpos($path,$hostname)===0) {
						$url='http://'.$path;
					}
					$i++;
					echo "
					<tr>
						<td>$i</td>
						<td><a href='$url' title='{$item[2]}' target='_blank'>$path</a></td>
						<td><strong>$value1</strong> ($percentage1)</td>
						<td><strong>$value2</strong> ($percentage2)</td>
						<td>$time</td>
					</tr>
					";
				}
				?>
				</tbody>
			</table>
		<?php endif ?>
	</div>
	<div class="span3">
		<h3><?php echo Yii::t('YcmModule.ycm','Traffic Sources'); ?></h3>
		<div id="trafficData"></div>
	</div>
</div>

<div class="row-fluid">
	<div class="span4">
		<h3><?php echo Yii::t('YcmModule.ycm','Overview'); ?></h3>
		<?php if (count($usage)>0): ?>
		<table class="table table-striped">
			<thead>
			<tr>
				<th><?php echo Yii::t('YcmModule.ycm','Metric'); ?></th>
				<th><?php echo Yii::t('YcmModule.ycm','Value'); ?></th>
			</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo Yii::t('YcmModule.ycm','Pageviews'); ?></td>
					<td><?php echo Yii::app()->numberFormatter->formatDecimal($usage['ga:pageviews']); ?></td>
				</tr>
				<tr>
					<td><?php echo Yii::t('YcmModule.ycm','Unique Pageviews'); ?></td>
					<td><?php echo Yii::app()->numberFormatter->formatDecimal($usage['ga:uniquePageviews']); ?></td>
				</tr>
				<tr>
					<td><?php echo Yii::t('YcmModule.ycm','Visits'); ?></td>
					<td><?php echo Yii::app()->numberFormatter->formatDecimal($usage['ga:visits']); ?></td>
				</tr>
				<tr>
					<td><?php echo Yii::t('YcmModule.ycm','Unique Visitors'); ?></td>
					<td><?php echo Yii::app()->numberFormatter->formatDecimal($usage['ga:visitors']); ?></td>
				</tr>
				<tr>
					<td><?php echo Yii::t('YcmModule.ycm','New Visitors'); ?></td>
					<td><?php echo Yii::app()->numberFormatter->formatDecimal($usage['ga:newVisits']); ?></td>
				</tr>
				<tr>
					<td><?php echo Yii::t('YcmModule.ycm','Pages / Visit'); ?></td>
					<td><?php
						if ($usage['ga:visits']>0) {
							echo Yii::app()->numberFormatter->formatDecimal($usage['ga:pageviews']/$usage['ga:visits']);
						} else {
							echo Yii::app()->numberFormatter->formatDecimal(0);
						}
						?></td>
				</tr>
				<tr>
					<td><?php echo Yii::t('YcmModule.ycm','New Visits'); ?></td>
					<td><?php
						if ($usage['ga:visits']>0) {
							echo Yii::app()->numberFormatter->formatPercentage($usage['ga:newVisits']/$usage['ga:visits']);
						} else {
							echo Yii::app()->numberFormatter->formatPercentage(0);
						}
						?></td>
				</tr>
				<tr>
					<td><?php echo Yii::t('YcmModule.ycm','Bounce Rate'); ?></td>
					<td><?php
						if ($usage['ga:entrances']>0) {
							echo Yii::app()->numberFormatter->formatPercentage($usage['ga:bounces']/$usage['ga:entrances']);
						} else {
							echo Yii::app()->numberFormatter->formatPercentage(0);
						}
						?></td>
				</tr>
				<tr>
					<td><?php echo Yii::t('YcmModule.ycm','Avg. Visit Duration'); ?></td>
					<td><?php
						if ($usage['ga:visits']>0) {
							echo gmdate('H:i:s',round($usage['ga:timeOnSite']/$usage['ga:visits']));
						} else {
							echo '00:00:00';
						}
						?></td>
				</tr>
				<tr>
					<td><?php echo Yii::t('YcmModule.ycm','Avg. Time on Page'); ?></td>
					<td><?php
						if ($usage['ga:visits']>0) {
							echo gmdate('H:i:s',round($usage['ga:timeOnPage']/($usage['ga:pageviews']-$usage['ga:exits'])));
						} else {
							echo '00:00:00';
						}
						?></td>
				</tr>
			</tbody>
		</table>
		<?php endif ?>
	</div>
	<div class="span4">
		<h3><?php echo Yii::t('YcmModule.ycm','Top Referrers'); ?></h3>
		<?php if (count($referrers)>0): ?>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>#</th>
				<th><?php echo Yii::t('YcmModule.ycm','Referrer'); ?></th>
				<th><?php echo Yii::t('YcmModule.ycm','Visits'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$i=0;
			foreach ($referrers as $item) {
				$i++;
				$value=Yii::app()->numberFormatter->formatDecimal($item[1]);
				echo "
				<tr>
					<td>$i</td>
					<td>{$item[0]}</td>
					<td>$value</td>
				</tr>
				";
			}
			?>
			</tbody>
		</table>
		<?php endif ?>
	</div>
	<div class="span4">
		<h3><?php echo Yii::t('YcmModule.ycm','Top Keywords'); ?></h3>
		<?php if (count($keywords)>0): ?>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>#</th>
				<th><?php echo Yii::t('YcmModule.ycm','Keyword'); ?></th>
				<th><?php echo Yii::t('YcmModule.ycm','Visits'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$i=0;
			foreach ($keywords as $item) {
				$i++;
				$value=Yii::app()->numberFormatter->formatDecimal($item[1]);
				echo "
				<tr>
					<td>$i</td>
					<td>{$item[0]}</td>
					<td>$value</td>
				</tr>
				";
			}
			?>
			</tbody>
		</table>
		<?php endif ?>
	</div>
</div>