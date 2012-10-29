<?php

/**
 * YcmModule
 * 
 * @uses CWebModule
 * @version 0.2-dev
 * @copyright 2012
 * @author Jani Mikkonen <janisto@php.net>
 * @license public domain
 */

class YcmModule extends CWebModule
{
	private $controller;
	private $_assetsUrl;
	private $_modelsList=array();
	protected $model;
	protected $registerModels=array();
	protected $excludeModels=array();
	protected $attributesWidgets=null;
	public $password;
	public $uploadPath;
	public $uploadUrl;
	public $uploadCreate=false;
	public $redactorUpload=true;
	public $permissions=0774;

	/**
	 * @param string $message the original message
	 * @return string the translated message
	 */
	public static function t($message)
	{
		return Yii::t('YcmModule.ycm',$message);
	}

	/**
	 * Init module.
	 */
	public function init()
	{
		if($this->uploadPath===null) {
			$path=Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'uploads';
			$this->uploadPath=realpath($path);
			if($this->uploadPath===false && $this->uploadCreate===true) {
				if (!mkdir($path,$this->permissions,true)) {
					throw new CHttpException(500,'Could not create "uploads" folder: '.$path.'.');
				}
			}
		}
		if($this->uploadUrl===null) {
			$this->uploadUrl=Yii::app()->request->baseUrl .'/uploads';
		}

		$this->setImport(array(
			$this->name.'.models.*',
			$this->name.'.components.*',
		));

		$this->configure(array(
			'preload'=>array('bootstrap'),
			'components'=>array(
				'bootstrap'=>array(
					'class'=>$this->name.'.extensions.bootstrap.components.Bootstrap',
					'responsiveCss'=>true,
				),
			),
		));
		$this->preloadComponents();

		Yii::app()->setComponents(array(
			'errorHandler'=>array(
				'errorAction'=>$this->name.'/default/error',
			),
			'user'=>array(
				'class'=>'CWebUser',
				'stateKeyPrefix'=>$this->name,
				'loginUrl'=>Yii::app()->createUrl($this->name.'/default/login'),
			),
		), true);
	}

	/**
	 * Get a list of all models.
	 *
	 * @return array Models
	 */
	public function getModelsList()
	{
		$models=$this->registerModels;

		if (!empty($models)) {
			foreach($models as $model) {
				Yii::import($model);
				if (substr($model, -1)=='*') {
					// Get a list of all models inside a directory. Example: 'application.models.*'
					$files=CFileHelper::findFiles(Yii::getPathOfAlias($model),array('fileTypes'=>array('php')));
					if ($files) {
						foreach($files as $file) {
							$modelName=str_replace('.php','',substr(strrchr($file,DIRECTORY_SEPARATOR), 1));
							$this->addModel($modelName);
						}
					}
				} else {
					$modelName=substr(strrchr($model, "."), 1);
					$this->addModel($modelName);
				}
			}
		}

		return array_unique($this->_modelsList);
	}

	/**
	 * Add to the list of models.
	 *
	 * @param string $model Model name
	 */
	protected function addModel($model)
	{
		$model=(string)$model;
		if (!in_array($model,$this->excludeModels)) {
			$this->_modelsList[]=$model;
		}
	}

	/**
	 * Load model.
	 *
	 * @param string $model Model name
	 * @return object Model
	 */
	public function loadModel($model)
	{
		$model=(string)$model;
		$this->model=new $model;
		return $this->model;
	}

	/**
	 * Create TbActiveForm widget.
	 *
	 * @param TbActiveForm $form
	 * @param object $model Model
	 * @param string $attribute Model attribute
	 */
	public function createWidget($form,$model,$attribute)
	{
		$lang=Yii::app()->language;
		if($lang=='en_us') {
			$lang='en';
		}

		$widget=$this->getAttributeWidget($attribute);
		switch ($widget) {
			case 'time':
				if ($form->type==TbActiveForm::TYPE_HORIZONTAL) {
					echo '<div class="control-group">';
					echo $form->labelEx($model,$attribute,array('class'=>'control-label'));
					echo '<div class="controls">';
				} else {
					echo $form->labelEx($model,$attribute);
				}
				echo '<div class="input-prepend"><span class="add-on"><i class="icon-time"></i></span>';
				$attributeOptions=array_slice($this->getAttributeOptions($attribute),2);
				$options=array(
					'model'=>$model,
					'attribute'=>$attribute,
					'language'=>$lang,
					'mode'=>'time',
					'htmlOptions'=>array('class'=>'size-medium'),
					'options'=>array(
						'timeFormat'=>'hh:mm:ss',
						'showSecond'=>true,
					),
				);
				if ($attributeOptions) {
					$options=array_merge($options,$attributeOptions);
				}
				$this->controller->widget($this->name.'.extensions.jui.EJuiDateTimePicker',$options);
				echo '</div>';
				echo $form->error($model,$attribute);
				if ($form->type==TbActiveForm::TYPE_HORIZONTAL) {
					echo '</div></div>';
				}
				break;

			case 'datetime':
				if ($form->type==TbActiveForm::TYPE_HORIZONTAL) {
					echo '<div class="control-group">';
					echo $form->labelEx($model,$attribute,array('class'=>'control-label'));
					echo '<div class="controls">';
				} else {
					echo $form->labelEx($model,$attribute);
				}
				echo '<div class="input-prepend"><span class="add-on"><i class="icon-calendar"></i></span>';
				$attributeOptions=array_slice($this->getAttributeOptions($attribute),2);
				$options=array(
					'model'=>$model,
					'attribute'=>$attribute,
					'language'=>$lang,
					'mode'=>'datetime',
					'htmlOptions'=>array('class'=>'size-medium'),
					'options'=>array(
						'dateFormat'=>'yy-mm-dd',
						'timeFormat'=>'hh:mm:ss',
						'showSecond'=>true,
						//'stepHour'=>'1',
						//'stepMinute'=>'10',
						//'stepSecond'=>'60',
					),
				);
				if ($attributeOptions) {
					$options=array_merge($options,$attributeOptions);
				}
				$this->controller->widget($this->name.'.extensions.jui.EJuiDateTimePicker',$options);
				echo '</div>';
				echo $form->error($model,$attribute);
				if ($form->type==TbActiveForm::TYPE_HORIZONTAL) {
					echo '</div></div>';
				}
				break;

			case 'date':
				if ($form->type==TbActiveForm::TYPE_HORIZONTAL) {
					echo '<div class="control-group">';
					echo $form->labelEx($model,$attribute,array('class'=>'control-label'));
					echo '<div class="controls">';
				} else {
					echo $form->labelEx($model,$attribute);
				}
				echo '<div class="input-prepend"><span class="add-on"><i class="icon-calendar"></i></span>';
				$attributeOptions=array_slice($this->getAttributeOptions($attribute),2);
				$options=array(
					'model'=>$model,
					'attribute'=>$attribute,
					'language'=>$lang,
					'mode'=>'date',
					'htmlOptions'=>array('class'=>'size-medium'),
					'options'=>array(
						'dateFormat'=>'yy-mm-dd',
					),
				);
				if ($attributeOptions) {
					$options=array_merge($options,$attributeOptions);
				}
				$this->controller->widget($this->name.'.extensions.jui.EJuiDateTimePicker',$options);
				echo '</div>';
				echo $form->error($model,$attribute);
				if ($form->type==TbActiveForm::TYPE_HORIZONTAL) {
					echo '</div></div>';
				}
				break;

			case 'wysiwyg':
				if ($form->type==TbActiveForm::TYPE_HORIZONTAL) {
					echo '<div class="control-group">';
					echo $form->labelEx($model,$attribute,array('class'=>'control-label'));
					echo '<div class="controls">';
				} else {
					echo $form->labelEx($model,$attribute);
				}
				$attributeOptions=array_slice($this->getAttributeOptions($attribute),2);
				$options=array(
					'model'=>$model,
					'attribute'=>$attribute,
					'options'=>array(
						'lang'=>$lang,
						'buttons'=>array(
							'formatting','|','bold','italic','deleted','|',
							'unorderedlist','orderedlist','outdent','indent','|',
							'image','link','|','html',
						),
					),
				);
				if ($attributeOptions) {
					$options=array_merge($options,$attributeOptions);
				}
				if($this->redactorUpload===true) {
					$redactorOptions=array(
						'options'=>array(
							'imageUpload'=>Yii::app()->createUrl($this->name.'/model/redactorImageUpload',array('name'=>get_class($model),'attr'=>$attribute)),
							'imageGetJson'=>Yii::app()->createUrl($this->name.'/model/redactorImageList',array('name'=>get_class($model),'attr'=>$attribute)),
							'imageUploadErrorCallback'=>new CJavaScriptExpression('function(obj,json) { alert(json.error); }'),
						),
					);
					$options=array_merge_recursive($options,$redactorOptions);
				}
				$this->controller->widget($this->name.'.extensions.redactor.ERedactorWidget',$options);
				echo $form->error($model,$attribute);
				if ($form->type==TbActiveForm::TYPE_HORIZONTAL) {
					echo '</div></div>';
				}
				break;

			case 'textArea':
				echo $form->textAreaRow($model,$attribute,array('rows'=>5,'cols'=>50,'class'=>'span8'));
				break;

			case 'textField':
				echo $form->textFieldRow($model,$attribute,array('class'=>'span5'));
				break;

			case 'chosen':
				$attributeOptions=array_slice($this->getAttributeOptions($attribute),2);
				$options=array(
					'empty'=>YcmModule::t('Choose').' '.$attribute,
					'class'=>'span5 chzn-select'
				);
				if ($attributeOptions) {
					$options=array_merge($options,$attributeOptions);
				}
				$this->controller->widget($this->name.'.extensions.chosen.EChosenWidget');
				echo $form->dropDownListRow($model,$attribute,$this->getAttributeChoices($attribute),$options);
				break;

			case 'chosenMultiple':
				$attributeOptions=array_slice($this->getAttributeOptions($attribute),2);
				$options=array(
					'data-placeholder'=>YcmModule::t('Choose').' '.$attribute,
					'multiple'=>'multiple',
					'class'=>'span5 chzn-select'
				);
				if ($attributeOptions) {
					$options=array_merge($options,$attributeOptions);
				}
				$this->controller->widget($this->name.'.extensions.chosen.EChosenWidget');
				echo $form->dropDownListRow($model,$attribute,$this->getAttributeChoices($attribute),$options);
				break;

			case 'dropDown':
				echo $form->dropDownListRow($model,$attribute,$this->getAttributeChoices($attribute),array('empty'=>YcmModule::t('Choose').' '.$attribute,'class'=>'span5'));
				break;

			case 'radioButton':
				echo $form->radioButtonListRow($model,$attribute,$this->getAttributeChoices($attribute));
				break;

			case 'boolean':
				echo $form->checkboxRow($model,$attribute);
				break;

			case 'password':
				echo $form->passwordFieldRow($model,$attribute,array('class'=>'span5'));
				break;

			case 'disabled':
				echo $form->textFieldRow($model,$attribute,array('class'=>'span5','disabled'=>true));
				break;

			case 'file':
				$columnName=$model->tableSchema->columns[$attribute]->name;
				$columnPath=$this->uploadUrl.'/'. strtolower(get_class($model)).'/'.$columnName.'/';
				if (!$model->isNewRecord && !empty($model->$columnName)) {
					$path=$columnPath.$model->$columnName;
					ob_start();
					echo '<p>';
					$this->controller->widget('bootstrap.widgets.TbButton', array(
						'label'=>YcmModule::t('Download'),
						'type'=>'',
						'url'=>$path,
					));
					echo '</p>';
					$html=ob_get_clean();
					echo $form->fileFieldRow($model,$columnName,array('class'=>'span5','hint'=>$html));
				} else {
					echo $form->fileFieldRow($model,$columnName,array('class'=>'span5'));
				}
				break;

			case 'image':
				$columnName=$model->tableSchema->columns[$attribute]->name;
				$columnPath=$this->uploadUrl.'/'. strtolower(get_class($model)).'/'.$columnName.'/';
				if (!$model->isNewRecord && !empty($model->$columnName)) {
					$path=$columnPath.$model->$columnName;
					$modalName='modal-image-'.$columnName;
					$image=CHtml::image($path,YcmModule::t('Image'),array('class'=>'modal-image'));
					ob_start();
					$this->controller->beginWidget('bootstrap.widgets.TbModal',array('id'=>$modalName));
					echo '<div class="modal-header"><a class="close" data-dismiss="modal">&times;</a><h4>'.YcmModule::t('Image preview').'</h4></div>';
					echo '<div class="modal-body">'.$image.'</div>';
					$this->controller->endWidget();
					echo '<p>';
					$this->controller->widget('bootstrap.widgets.TbButton', array(
						'label'=>YcmModule::t('Preview'),
						'type'=>'',
						'htmlOptions'=>array(
							'data-toggle'=>'modal',
							'data-target'=>'#'.$modalName,
						),
					));
					echo '</p>';
					$html=ob_get_clean();
					echo $form->fileFieldRow($model,$columnName,array('class'=>'span5','hint'=>$html));
				} else {
					echo $form->fileFieldRow($model,$columnName,array('class'=>'span5'));
				}
				break;

			default:
				echo $form->textFieldRow($model,$attribute,array('class'=>'span5'));
				break;
		}
	}

	/**
	 * Get attributes widget.
	 *
	 * @param string $attribute Model attribute
	 * @return null|string
	 */
	public function getAttributeWidget($attribute)
	{
		if ($this->attributesWidgets!==null) {
			if (isset($this->attributesWidgets->$attribute)) {
				return $this->attributesWidgets->$attribute;
			} else {
				$dbType=$this->model->tableSchema->columns[$attribute]->dbType;
				if ($dbType=='text') {
					return 'wysiwyg';
				} else {
					return 'textField';
				}
			}
		}

		if (method_exists($this->model,'attributeWidgets')) {
			$attributeWidgets=$this->model->attributeWidgets();
		} else {
			return null;
		}

		$data=array();
		if (!empty($attributeWidgets)) {
			foreach($attributeWidgets as $item) {
				if (isset($item[0]) && isset($item[1])) {
					$data[$item[0]]=$item[1];
					$data[$item[0].'Options']=$item;
				}
			}
		}
		//print_r($data);
		$this->attributesWidgets=(object)$data;

		return $this->getAttributeWidget($attribute);
	}

	/**
	 * Get attributes data.
	 *
	 * @param string $attribute Model attribute
	 * @return null
	 */
	protected function getAttributeOptions($attribute)
	{
		$optionsName=(string)$attribute.'Options';
		if (isset($this->attributesWidgets->$optionsName)) {
			return $this->attributesWidgets->$optionsName;
		} else {
			return null;
		}
	}

	/**
	 * Get an array of attribute choice values.
	 * The variable or method name needs ​​to be: attributeChoices.
	 *
	 * @param string $attribute Model attribute
	 * @return array
	 */
	private function getAttributeChoices($attribute)
	{
		$data=array();
		$choicesName=(string)$attribute.'Choices';
		if (method_exists($this->model, $choicesName) && is_array($this->model->$choicesName())) {
			$data=$this->model->$choicesName();
		} else if (isset($this->model->$choicesName) && is_array($this->model->$choicesName)) {
			$data=$this->model->$choicesName;
		}
		return $data;
	}

	/**
	 * Get model's administrative name.
	 *
	 * @param mixed $model
	 * @return string
	 */
	public function getAdminName($model)
	{
		if (is_string($model)) {
			$model=new $model;
		}
		if (!isset($model->adminNames)) {
			return get_class($model);
		} else {
			return $model->adminNames[0];
		}
	}

	/**
	 * Get model's singular name.
	 *
	 * @param mixed $model
	 * @return string
	 */
	public function getSingularName($model)
	{
		if (is_string($model)) {
			$model=new $model;
		}
		if (!isset($model->adminNames)) {
			return strtolower(get_class($model));
		} else {
			return $model->adminNames[1];
		}
	}

	/**
	 * Get model's plural name.
	 *
	 * @param mixed $model
	 * @return string
	 */
	public function getPluralName($model)
	{
		if (is_string($model)) {
			$model=new $model;
		}
		if (!isset($model->adminNames)) {
			return strtolower(get_class($model));
		} else {
			return $model->adminNames[2];
		}
	}

	/**
	 * Download Excel?
	 *
	 * @param mixed $model
	 * @return bool
	 */
	public function getDownloadExcel($model)
	{
		if (is_string($model)) {
			$model=new $model;
		}
		if (isset($model->downloadExcel)) {
			return $model->downloadExcel;
		} else {
			return false;
		}
	}

	/**
	 * Download MS CSV?
	 *
	 * @param mixed $model
	 * @return bool
	 */
	public function getDownloadMsCsv($model)
	{
		if (is_string($model)) {
			$model=new $model;
		}
		if (isset($model->downloadMsCsv)) {
			return $model->downloadMsCsv;
		} else {
			return false;
		}
	}

	/**
	 * Download CSV?
	 *
	 * @param mixed $model
	 * @return bool
	 */
	public function getDownloadCsv($model)
	{
		if (is_string($model)) {
			$model=new $model;
		}
		if (isset($model->downloadCsv)) {
			return $model->downloadCsv;
		} else {
			return false;
		}
	}

	/**
	 * @return string the base URL that contains all published asset files of the module.
	 */
	public function getAssetsUrl()
	{
		if ($this->_assetsUrl === null) {
			$this->_assetsUrl=Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias($this->name.'.assets'));
		}
		return $this->_assetsUrl;
	}

	/**
	 * @param string $value the base URL that contains all published asset files of the module.
	 */
	public function setAssetsUrl($value)
	{
		$this->_assetsUrl=$value;
	}

	/**
	 * @param CController $controller
	 * @param CAction $action
	 * @return bool
	 */
	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action)) {
			// this method is called before any module controller action is performed
			$this->controller=$controller;
			$route=$controller->id.'/'.$action->id;
			$publicPages=array(
				'default/login',
				'default/error',
			);
			if($this->password!==false && Yii::app()->user->isGuest && !in_array($route,$publicPages)) {
				Yii::app()->user->loginRequired();
			} else {
				return true;
			}
		}
		return false;
	}
}