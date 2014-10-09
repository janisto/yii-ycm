<?php

/**
 * Chosen widget
 *
 * @author Jani Mikkonen <janisto@php.net>
 * @version 1.6.1
 * @license public domain (http://unlicense.org)
 * @package extensions.chosen
 * @link http://harvesthq.github.com/chosen/
 */

class EChosenWidget extends CWidget
{
	/**
	 * Assets package ID.
	 */
	const PACKAGE_ID = 'chosen-widget';

	/**
	 * @var string path to assets
	 */
	protected $assetsPath;

	/**
	 * @var string URL to assets
	 */
	protected $assetsUrl;

	/**
	 * @var array chosen options
	 * @see http://harvesthq.github.com/chosen/
	 */
	public $options = array();

	/**
	 * @var string select selector for jQuery
	 */
	public $selector = '.chosen-select';

	/**
	 * Init widget
	 */
	public function init()
	{
		parent::init();
		if ($this->assetsPath === null) {
			$this->assetsPath = dirname(__FILE__).DIRECTORY_SEPARATOR.'assets';
		}
		if ($this->assetsUrl === null) {
			$this->assetsUrl = Yii::app()->assetManager->publish($this->assetsPath);
		}
		$this->registerClientScript();
	}

	/**
	 * Register CSS and scripts.
	 */
	protected function registerClientScript()
	{
		$cs = Yii::app()->clientScript;
		if (!isset($cs->packages[self::PACKAGE_ID])) {
			$cs->packages[self::PACKAGE_ID] = array(
				'basePath' => $this->assetsPath,
				'baseUrl' => $this->assetsUrl,
				'js' => array(
					'js/chosen.jquery' . (YII_DEBUG ? '' : '.min') . '.js',
				),
				'css' => array(
					'css/chosen' . (YII_DEBUG ? '' : '.min') . '.css',
				),
				'depends' => array(
					'jquery',
				),
			);
		}
		$cs->registerPackage(self::PACKAGE_ID);
		$cs->registerScript(
			__CLASS__ . '#' . $this->id,
			'jQuery('. CJavaScript::encode($this->selector) .').chosen('. CJavaScript::encode($this->options) .');',
			CClientScript::POS_READY
		);
	}
}