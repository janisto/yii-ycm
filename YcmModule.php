<?php

/**
 * YcmModule
 * 
 * @uses CWebModule
 * @version 0.5-dev
 * @copyright 2012
 * @author Jani Mikkonen <janisto@php.net>
 * @license public domain
 */

class YcmModule extends CWebModule
{
	private $controller;
	private $_assetsUrl;
	private $_modelsList=array();
	protected $registerModels=array();
	protected $excludeModels=array();
	protected $attributesWidgets;
	public $username;
	public $password;
	public $uploadPath;
	public $uploadUrl;
	public $uploadCreate=false;
	public $redactorUpload=false;
	public $permissions=0774;

	/**
	 * Load model.
	 *
	 * @param string $name Model name
	 * @param null|int $pk Primary key
	 * @throws CHttpException
	 * @return object Model
	 */
	public function loadModel($name,$pk=null)
	{
		$name=(string)$name;
		$model=new $name;
		if ($pk!==null) {
			$model=$model->findByPk((int)$pk);
			if ($model===null) {
				throw new CHttpException(500,Yii::t(
					'YcmModule.ycm',
					'Could not load model "{name}".',
					array('{name}'=>$name)
				));
			}
		}
		$model->attachBehavior('admin',array('class'=>'application.modules.ycm.behaviors.FileBehavior'));
		return $model;
	}

	/**
	 * Init module.
	 */
	public function init()
	{
		if ($this->uploadPath===null) {
			$path=Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'uploads';
			$this->uploadPath=realpath($path);
			if ($this->uploadPath===false && $this->uploadCreate===true) {
				if (!mkdir($path,$this->permissions,true)) {
					throw new CHttpException(500,Yii::t(
						'YcmModule.ycm',
						'Could not create upload folder "{dir}".',
						array('{dir}'=>$path)
					));
				}
			}
		}
		if ($this->uploadUrl===null) {
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
				'allowAutoLogin'=>true,
				'stateKeyPrefix'=>$this->name,
				'loginUrl'=>Yii::app()->createUrl($this->name.'/default/login'),
			),
		), true);
	}

	/**
	 * Get a list of all models.
	 *
	 * @return array Model names
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
	 * Create TbActiveForm widget.
	 *
	 * @param TbActiveForm $form
	 * @param object $model Model
	 * @param string $attribute Model attribute
	 */
	public function createWidget($form,$model,$attribute)
	{
		$lang=Yii::app()->language;
		if ($lang=='en_us') {
			$lang='en';
		}

		$widget=$this->getAttributeWidget($model,$attribute);
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
				if ($this->redactorUpload===true) {
					$redactorOptions=array(
						'options'=>array(
							'imageUpload'=>Yii::app()->createUrl($this->name.'/model/redactorImageUpload',array(
								'name'=>get_class($model),
								'attr'=>$attribute)
							),
							'imageGetJson'=>Yii::app()->createUrl($this->name.'/model/redactorImageList',array(
								'name'=>get_class($model),
								'attr'=>$attribute)
							),
							'imageUploadErrorCallback'=>new CJavaScriptExpression(
								'function(obj,json) { alert(json.error); }'
							),
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
					'empty'=>Yii::t('YcmModule.ycm',
						'Choose {name}',
						array('{name}'=>$model->getAttributeLabel($attribute))
					),
					'class'=>'span5 chzn-select'
				);
				if ($attributeOptions) {
					$options=array_merge($options,$attributeOptions);
				}
				$this->controller->widget($this->name.'.extensions.chosen.EChosenWidget');
				echo $form->dropDownListRow($model,$attribute,$this->getAttributeChoices($model,$attribute),$options);
				break;

			case 'chosenMultiple':
				$attributeOptions=array_slice($this->getAttributeOptions($attribute),2);
				$options=array(
					'data-placeholder'=>Yii::t('YcmModule.ycm',
						'Choose {name}',
						array('{name}'=>$model->getAttributeLabel($attribute))
					),
					'multiple'=>'multiple',
					'class'=>'span5 chzn-select'
				);
				if ($attributeOptions) {
					$options=array_merge($options,$attributeOptions);
				}
				$this->controller->widget($this->name.'.extensions.chosen.EChosenWidget');
				echo $form->dropDownListRow($model,$attribute,$this->getAttributeChoices($model,$attribute),$options);
				break;

			case 'dropDown':
				echo $form->dropDownListRow($model,$attribute,$this->getAttributeChoices($model,$attribute),array(
					'empty'=>Yii::t('YcmModule.ycm',
						'Choose {name}',
						array('{name}'=>$model->getAttributeLabel($attribute))
					),
					'class'=>'span5')
				);
				break;

			case 'typeHead':
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
					'htmlOptions'=>array('class'=>'span5','autocomplete'=>'off'),
					'options'=>array(
						'name'=>'typeahead',
						'source'=>$this->getAttributeChoices($model,$attribute),
						'matcher'=>"js:function(item) {
							return ~item.toLowerCase().indexOf(this.query.toLowerCase());
						}",
					),
				);
				if ($attributeOptions) {
					$options=array_merge_recursive($options,$attributeOptions);
				}
				$this->controller->widget('bootstrap.widgets.TbTypeahead',$options);
				echo $form->error($model,$attribute);
				if ($form->type==TbActiveForm::TYPE_HORIZONTAL) {
					echo '</div></div>';
				}
				break;

			case 'radioButton':
				echo $form->radioButtonListRow($model,$attribute,$this->getAttributeChoices($model,$attribute));
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
				if (!$model->isNewRecord && !empty($model->$attribute)) {
					ob_start();
					echo '<p>';
					$this->controller->widget('bootstrap.widgets.TbButton', array(
						'label'=>Yii::t('YcmModule.ycm','Download'),
						'type'=>'',
						'url'=>$model->getFileUrl($attribute),
					));
					echo '</p>';
					$html=ob_get_clean();
					echo $form->fileFieldRow($model,$attribute,array('class'=>'span5','hint'=>$html));
				} else {
					echo $form->fileFieldRow($model,$attribute,array('class'=>'span5'));
				}
				break;

			case 'image':
				if (!$model->isNewRecord && !empty($model->$attribute)) {
					$modalName='modal-image-'.$attribute;
					$image=CHtml::image($model->getFileUrl($attribute),Yii::t('YcmModule.ycm','Image'),array(
						'class'=>'modal-image')
					);
					ob_start();
					$this->controller->beginWidget('bootstrap.widgets.TbModal',array('id'=>$modalName));
					echo '<div class="modal-header"><a class="close" data-dismiss="modal">&times;</a><h4>';
					echo Yii::t('YcmModule.ycm','Image preview').'</h4></div>';
					echo '<div class="modal-body">'.$image.'</div>';
					$this->controller->endWidget();
					echo '<p>';
					$this->controller->widget('bootstrap.widgets.TbButton', array(
						'label'=>Yii::t('YcmModule.ycm','Preview'),
						'type'=>'',
						'htmlOptions'=>array(
							'data-toggle'=>'modal',
							'data-target'=>'#'.$modalName,
						),
					));
					echo '</p>';
					$html=ob_get_clean();
					echo $form->fileFieldRow($model,$attribute,array('class'=>'span5','hint'=>$html));
				} else {
					echo $form->fileFieldRow($model,$attribute,array('class'=>'span5'));
				}
				break;

			default:
				echo $form->textFieldRow($model,$attribute,array('class'=>'span5'));
				break;
		}
	}

	/**
	 * Get attribute file path.
	 *
	 * @param string $name Model name
	 * @param string $attribute Model attribute
	 * @return string Model attribute file path
	 */
	public function getAttributePath($name,$attribute)
	{
		return $this->uploadPath.DIRECTORY_SEPARATOR.strtolower($name).DIRECTORY_SEPARATOR.strtolower($attribute);
	}

	/**
	 * Get attribute file URL.
	 *
	 * @param string $name Model name
	 * @param string $attribute Model attribute
	 * @param string $file Filename
	 * @return string Model attribute file URL
	 */
	public function getAttributeUrl($name,$attribute,$file)
	{
		return $this->uploadUrl.'/'.strtolower($name).'/'.strtolower($attribute).'/'.$file;
	}

	/**
	 * Get attributes widget.
	 *
	 * @param object $model Model
	 * @param string $attribute Model attribute
	 * @return null|object
	 */
	public function getAttributeWidget($model,$attribute)
	{
		if ($this->attributesWidgets!==null) {
			if (isset($this->attributesWidgets->$attribute)) {
				return $this->attributesWidgets->$attribute;
			} else {
				$dbType=$model->tableSchema->columns[$attribute]->dbType;
				if ($dbType=='text') {
					return 'wysiwyg';
				} else {
					return 'textField';
				}
			}
		}

		if (method_exists($model,'attributeWidgets')) {
			$attributeWidgets=$model->attributeWidgets();
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

		$this->attributesWidgets=(object)$data;

		return $this->getAttributeWidget($model,$attribute);
	}

	/**
	 * Get an array of attribute choice values.
	 * The variable or method name needs ​​to be: attributeChoices.
	 *
	 * @param object $model Model
	 * @param string $attribute Model attribute
	 * @return array
	 */
	private function getAttributeChoices($model,$attribute)
	{
		$data=array();
		$choicesName=(string)$attribute.'Choices';
		if (method_exists($model,$choicesName) && is_array($model->$choicesName())) {
			$data=$model->$choicesName();
		} else if (isset($model->$choicesName) && is_array($model->$choicesName)) {
			$data=$model->$choicesName;
		}
		return $data;
	}

	/**
	 * Get attributes data.
	 *
	 * @param string $attribute Model attribute
	 * @return null|array
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
		if (parent::beforeControllerAction($controller, $action)) {
			// this method is called before any module controller action is performed
			$this->controller=$controller;
			$route=$controller->id.'/'.$action->id;
			$publicPages=array(
				'default/login',
				'default/error',
			);
			if ($this->password!==false && Yii::app()->user->isGuest && !in_array($route,$publicPages)) {
				Yii::app()->user->loginRequired();
			} else {
				return true;
			}
		}
		return false;
	}
}