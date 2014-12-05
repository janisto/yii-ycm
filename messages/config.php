<?php
/**
 * This is the configuration for generating message translations
 * for the ycm module. It is used by the 'yiic message' command.
 *
 * Usage:
 * $ cd path/to/protected/modules/ycm
 * $ php ../../yiic message messages/config.php
 */
return array(
	'sourcePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'messagePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'messages',
	'languages'=>array('de','fi','ru','zh_cn'),
	'fileTypes'=>array('php'),
	'overwrite'=>false,
	'removeOld'=>true,
	'sort'=>true,
	'translator'=>'Yii::t',
	'exclude'=>array(
		'.svn',
		'.git',
		'.gitignore',
		'/extensions',
		'/vendors',
	),
);
