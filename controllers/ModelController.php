<?php

class ModelController extends AdminController
{
	/**
	 * @var string Default action name
	 */
	public $defaultAction='list';

	/**
	 * Suggests tags via AJAX based on the current user input.
	 *
	 * @param string $name Model name
	 * @param string $attr Model attribute
	 */
	public function actionSuggestTags($name,$attr)
	{
		$attribute=(string)$attr;
		$model=$this->module->loadModel($name);
		if (isset($_GET['q']) && ($keyword=trim($_GET['q']))!=='') {
			$criteria=new CDbCriteria(array(
				'limit'=>15,
				'order'=>'count DESC, name',
			));
			$criteria->addSearchCondition('name',$keyword);
			$tags=$model->$attribute->getAllTags($criteria);
			if (!empty($tags)) {
				echo implode("\n",$tags);
			}
		}
	}

	/**
	 * Redactor image upload.
	 *
	 * @param string $name Model name
	 * @param string $attr Model attribute
	 * @throws CHttpException
	 */
	public function actionRedactorImageUpload($name,$attr)
	{
		$name=(string)$name;
		$attribute=(string)$attr;

		// Make Yii think this is a AJAX request.
		$_SERVER['HTTP_X_REQUESTED_WITH']='XMLHttpRequest';

		$file=CUploadedFile::getInstanceByName('file');
		if ($file instanceof CUploadedFile) {
			if (!in_array(strtolower($file->getExtensionName()),array('gif','png','jpg','jpeg'))) {
				throw new CHttpException(500,CJSON::encode(array('error'=>Yii::t(
					'YcmModule.ycm',
					'Invalid file extension "{ext}".',
					array('{ext}'=>$file->getExtensionName())
				))));
			}
			$fileName=trim(md5($attribute.time().uniqid(rand(),true))).'.'.$file->getExtensionName();
			$attributePath=$this->module->getAttributePath($name,$attribute);
			if (!is_dir($attributePath)) {
				if (!mkdir($attributePath,$this->module->permissions,true)) {
					throw new CHttpException(500,CJSON::encode(array('error'=>Yii::t(
						'YcmModule.ycm',
						'Could not create folder "{dir}". Make sure "uploads" folder is writable.',
						array('{dir}'=>$attributePath)
					))));
				}
			}
			$path=$attributePath.DIRECTORY_SEPARATOR.$fileName;
			if (file_exists($path) || !$file->saveAs($path)) {
				throw new CHttpException(500,CJSON::encode(array('error'=>Yii::t(
					'YcmModule.ycm',
					'Could not save file or file exists: "{file}".',
					array('{file}'=>$path)
				))));
			}
			$attributeUrl=$this->module->getAttributeUrl($name,$attribute,$fileName);
			$data=array(
				'filelink'=>$attributeUrl,
			);
			echo CJSON::encode($data);
			exit();
		} else {
			throw new CHttpException(500,CJSON::encode(array('error'=>Yii::t(
				'YcmModule.ycm',
				'Could not upload file.'
			))));
		}
	}

	/**
	 * Redactor image list.
	 *
	 * @param string $name Model name
	 * @param string $attr Model attribute
	 */
	public function actionRedactorImageList($name,$attr)
	{
		$name=(string)$name;
		$attribute=(string)$attr;
		$attributePath=$this->module->getAttributePath($name,$attribute);
		$files=CFileHelper::findFiles($attributePath,array('fileTypes'=>array('gif','png','jpg','jpeg')));
		$data=array();
		if ($files) {
			foreach ($files as $file) {
				$fileUrl=$this->module->getAttributeUrl($name,$attribute,basename($file));
				$data[]=array(
					'thumb'=>$fileUrl,
					'image'=>$fileUrl,
				);
			}
		}
		echo CJSON::encode($data);
		exit();
	}

	/**
	 * List models.
	 *
	 * @param string $name Model name
	 */
	public function actionList($name)
	{
		$model=$this->module->loadModel($name);

		// unset default model values
		$model->unsetAttributes();

		if (isset($_GET[get_class($model)])){
			// Do not set unsafe attributes
			$params=$_GET[get_class($model)];
			foreach ($params as $key=>$val) {
				if (!$model->isAttributeSafe($key)){
					unset($params[$key]);
				}
			}
			$model->attributes=$params;
		}

		$this->breadcrumbs=array(
			$this->module->getAdminName($model),
		);

		$data1=array();
		if (method_exists($model,'adminSearch')) {
			$data1=$model->adminSearch();
		}

		$urlPrefix='Yii::app()->createUrl("'.$this->module->name.'/model/';
		$data2=array(
			'id'=>'objects-grid',
			'type'=>'striped bordered condensed',
			'dataProvider'=>$model->search(),
			'filter'=>$model,
			'pager'=>array(
				'class'=>'bootstrap.widgets.TbPager',
				'displayFirstAndLast'=>true,
				'firstPageLabel'=>'&lang;&lang;',
				'prevPageLabel'=>'&lang;',
				'nextPageLabel'=>'&rang;',
				'lastPageLabel'=>'&rang;&rang;',
			),
			'columns'=>array(
				array(
					'class'=>'bootstrap.widgets.TbButtonColumn',
					'updateButtonUrl'=>$urlPrefix.'update",array("name"=>"'.get_class($model).'","pk"=>$data->primaryKey))',
					'deleteButtonUrl'=>$urlPrefix.'delete",array("name"=>"'.get_class($model).'","pk"=>$data->primaryKey))',
					'viewButtonUrl'=>$urlPrefix.'view",array("name"=>"'.get_class($model).'","pk"=>$data->primaryKey))',
					'viewButtonOptions'=>array(
						'style'=>'display:none;',
					),
				),
			),
		);

		$data=array_merge_recursive($data1,$data2);

		if (Yii::app()->request->isAjaxRequest) {
			$this->widget('bootstrap.widgets.TbGridView',$data);
			Yii::app()->end();
		}

		$this->render('list',array(
			'title'=>$this->module->getAdminName($model),
			'model'=>$model,
			'data'=>$data,
		));
	}

	/**
	 * Create model.
	 *
	 * @param string $name Model name
	 * @throws CHttpException
	 */
	public function actionCreate($name)
	{
		$model=$this->module->loadModel($name);

		if (Yii::app()->request->isPostRequest && isset($_POST[$name])) {
			$paths=array();
			$model->attributes=$_POST[$name];

			foreach ($model->tableSchema->columns as $column) {
				$attribute=$column->name;
				$widget=$this->module->getAttributeWidget($model,$attribute);
				if ($widget=='file' || $widget=='image') {
					$file=CUploadedFile::getInstance($model,$attribute);
					if ($file instanceof CUploadedFile) {
						$fileName=trim(md5($attribute.time().uniqid(rand(),true))).'.'.$file->getExtensionName();
						$attributePath=$this->module->getAttributePath($name,$attribute);
						if (!is_dir($attributePath)) {
							if (!mkdir($attributePath,$this->module->permissions,true)) {
								throw new CHttpException(500,Yii::t(
									'YcmModule.ycm',
									'Could not create folder "{dir}". Make sure "uploads" folder is writable.',
									array('{dir}'=>$attributePath)
								));
							}
						}
						$path=$attributePath.DIRECTORY_SEPARATOR.$fileName;
						if (file_exists($path) || !$file->saveAs($path)) {
							throw new CHttpException(500,Yii::t(
								'YcmModule.ycm',
								'Could not save file or file exists: "{file}".',
								array('{file}'=>$path)
							));
						}
						array_push($paths,$path);
						$model->$attribute=$fileName;
					}
				}
			}

			$behaviors=$model->behaviors();
			if (!empty($behaviors)) {
				foreach ($behaviors as $key=>$behavior) {
					if (substr_count($behavior['class'],'.ETaggableBehavior')>0 && isset($_POST[$key])) {
						$model->$key->setTags($_POST[$key]);
					}
				}
			}

			if ($model->save()) {
				Yii::app()->user->setFlash('success',Yii::t('YcmModule.ycm','Changes saved.'));
				$this->redirectUser($name,$model->primaryKey);
			} else if (count($paths)!=0) {
				foreach ($paths as $path) {
					if (file_exists($path)) {
						@unlink($path); // Save failed - delete files.
					}
				}
			}
		}

		$title=Yii::t('YcmModule.ycm',
			'Create {name}',
			array('{name}'=>$this->module->getSingularName($model))
		);
		$this->breadcrumbs=array(
			$this->module->getAdminName($model)=>$this->createUrl('model/list',array('name'=>$name)),
			$title,
		);

		$this->render('form',array(
			'title'=>$title,
			'model'=>$model,
		));
	}

	/**
	 * Update model.
	 *
	 * @param string $name Model name
	 * @param integer $pk Primary key
	 * @throws CHttpException
	 */
	public function actionUpdate($name,$pk)
	{
		$model=$this->module->loadModel($name,$pk);

		if (Yii::app()->request->isPostRequest && isset($_POST[$name])) {
			$paths=array();
			$oldPaths=array();
			$deleteOld=array();

			// store old files to array
			foreach ($model->tableSchema->columns as $column) {
				$attribute=$column->name;
				$widget=$this->module->getAttributeWidget($model,$attribute);
				if ($widget=='file' || $widget=='image') {
					$attributePath=$this->module->getAttributePath($name,$attribute);
					if (isset($model->$attribute) && !empty($model->$attribute)) {
						$path=$attributePath.DIRECTORY_SEPARATOR.$model->$attribute;
						$oldPaths[$attribute]=$path;
					}
				}
			}

			// set attributes from POST
			$model->attributes=$_POST[$name];

			foreach ($model->tableSchema->columns as $column) {
				$attribute=$column->name;
				$widget=$this->module->getAttributeWidget($model,$attribute);
				if ($widget=='file' || $widget=='image') { // file or image
					$file=CUploadedFile::getInstance($model,$attribute);
					if ($file instanceof CUploadedFile) {
						$fileName=trim(md5($attribute.time().uniqid(rand(),true))).'.'.$file->getExtensionName();
						$attributePath=$this->module->getAttributePath($name,$attribute);
						if (!is_dir($attributePath)) {
							if (!mkdir($attributePath,$this->module->permissions,true)) {
								throw new CHttpException(500,Yii::t(
									'YcmModule.ycm',
									'Could not create folder "{dir}". Make sure "uploads" folder is writable.',
									array('{dir}'=>$attributePath)
								));
							}
						}
						$path=$attributePath.DIRECTORY_SEPARATOR.$fileName;
						if (file_exists($path) || !$file->saveAs($path)) {
							throw new CHttpException(500,Yii::t(
								'YcmModule.ycm',
								'Could not save file or file exists: "{file}".',
								array('{file}'=>$path)
							));
						}
						array_push($paths,$path);
						array_push($deleteOld,$attribute);
						$model->$attribute=$fileName;
					}
				}
			}

			$behaviors=$model->behaviors();
			if (!empty($behaviors)) {
				foreach ($behaviors as $key=>$behavior) {
					if (substr_count($behavior['class'],'.ETaggableBehavior')>0 && isset($_POST[$key])) {
						$model->$key->setTags($_POST[$key]);
					}
				}
			}

			if ($model->save()) {
				if (count($deleteOld)!=0) {
					foreach ($deleteOld as $old) {
						if (isset($oldPaths[$old]) && file_exists($oldPaths[$old])) {
							@unlink($oldPaths[$old]);
						}
					}
				}
				Yii::app()->user->setFlash('success',Yii::t('YcmModule.ycm','Changes saved.'));
				$this->redirectUser($name,$model->primaryKey);
			} else if (count($paths)!=0) {
				foreach ($paths as $path) {
					if (file_exists($path)) {
						@unlink($path); // Save failed - delete files.
					}
				}
			}
		}

		$title=Yii::t('YcmModule.ycm',
			'Edit {name}',
			array('{name}'=>$this->module->getSingularName($model))
		);
		$this->breadcrumbs=array(
			$this->module->getAdminName($model)=>$this->createUrl('model/list',array('name'=>$name)),
			$title,
		);

		$this->render('form',array(
			'title'=>Yii::t('YcmModule.ycm',
				'Edit {name}',
				array('{name}'=>$this->module->getSingularName($model))
			),
			'model'=>$model,
		));
	}

	/**
	 * Delete model.
	 *
	 * @param string $name Model name
	 * @param integer $pk Primary key
	 * @throws CHttpException
	 * @return void
	 */
	public function actionDelete($name,$pk)
	{
		$model=$this->module->loadModel($name,$pk);

		if ($model!==null) {
			$model->delete();

			// delete files
			foreach ($model->tableSchema->columns as $column) {
				$attribute=$column->name;
				$widget=$this->module->getAttributeWidget($model,$attribute);
				if ($widget=='file' || $widget=='image') { // file or image
					$attributePath=$this->module->getAttributePath($name,$attribute);
					if (isset($model->$attribute)) {
						$path=$attributePath.DIRECTORY_SEPARATOR.$model->$attribute;
						if (file_exists($path)) {
							@unlink($path); // Delete file.
						}
					}
				}
			}
		} else {
			Yii::app()->user->setFlash('error',Yii::t(
				'YcmModule.ycm',
				'Could not delete entry "{name}" with an ID "{pk}".',
				array('{name}'=>$name,'{pk}'=>$pk)
			));
		}
		$this->redirect($this->createUrl('model/list',array('name'=>$name)));
	}

	/**
	 * Redirect after editing model data.
	 *
	 * @param string $name Model name
	 * @param integer $pk Primary key
	 */
	protected function redirectUser($name,$pk)
	{
		if (isset($_POST['_save'])) {
			$this->redirect($this->createUrl('model/list',array('name'=>$name)));
		} else if (isset($_POST['_addanother'])) {
			Yii::app()->user->setFlash('success',Yii::t('YcmModule.ycm','Changes saved. You can add a new entry.'));
			$this->redirect($this->createUrl('model/create',array('name'=>$name)));
		} else if (isset($_POST['_continue'])) {
			$this->redirect($this->createUrl('model/update',array('name'=>$name,'pk'=>$pk)));
		}
	}

	/**
	 * Download CSV.
	 *
	 * @param string $name Model name
	 */
	public function actionCsv($name)
	{
		$model=$this->module->loadModel($name);

		$memoryLimit=5*1024*1024;
		$delimiter=";";
		$enclosure='"';
		$header=array();
		$select='';

		foreach ($model->tableSchema->columns as $column) {
			// skip primary key?
			//if ($column->isPrimaryKey===true) continue;

			// no new lines in CSV format.
			$header[]=(string)str_replace(array("\r","\r\n","\n"),'',trim($model->getAttributeLabel($column->name)));

			if ($select!='') {
				$select.=', ';
			}
			$select.=Yii::app()->db->quoteColumnName($column->name);
		}

		$provider=Yii::app()->db->createCommand('SELECT '.$select.' FROM '.$model->tableSchema->name)->queryAll();

		// memory limit before php://temp starts using a temporary file
		$fp=fopen("php://temp/maxmemory:$memoryLimit",'w');

		// header line
		fputcsv($fp,$header,$delimiter,$enclosure);

		// content lines
		foreach ($provider as $row) {
			$retVal=array();
			foreach ($row as $item) {
				if ($item==0 || !empty($item)) {
					// no new lines in CSV format.
					$retVal[]=(string)str_replace(array("\r","\r\n","\n"),'',trim($item));
				} else {
					$retVal[]='';
				}
			}
			fputcsv($fp,$retVal,$delimiter,$enclosure);
		}

		rewind($fp);
		$content=stream_get_contents($fp);
		$filename=$name.'_'.date('Y-m-d').'.csv';
		Yii::app()->getRequest()->sendFile($filename,$content,"text/csv",false);
		exit;
	}

	/**
	 * Download Microsoft formatted CSV.
	 *
	 * @param string $name Model name
	 */
	public function actionMsCsv($name)
	{
		$model=$this->module->loadModel($name);

		$memoryLimit=5*1024*1024;
		$delimiter="\t"; // UTF-16LE needs "\t"
		$enclosure='"';
		$header=array();
		$select='';

		foreach ($model->tableSchema->columns as $column) {
			// skip primary key?
			//if ($column->isPrimaryKey===true) continue;

			// no new lines in CSV format.
			$header[]=(string)str_replace(array("\r","\r\n","\n"),'',trim($model->getAttributeLabel($column->name)));

			if ($select!='') {
				$select.=', ';
			}
			$select.=Yii::app()->db->quoteColumnName($column->name);
		}

		$provider=Yii::app()->db->createCommand('SELECT '.$select.' FROM '.$model->tableSchema->name)->queryAll();

		// memory limit before php://temp starts using a temporary file
		$fp=fopen("php://temp/maxmemory:$memoryLimit",'w');

		// header line
		fputcsv($fp,$header,$delimiter,$enclosure);

		// content lines
		foreach ($provider as $row) {
			$retVal=array();
			foreach ($row as $item) {
				if ($item==0 || !empty($item)) {
					// no new lines in CSV format.
					$retVal[]=(string)str_replace(array("\r","\r\n","\n"),'',trim($item));
				} else {
					$retVal[]='';
				}
			}
			fputcsv($fp,$retVal,$delimiter,$enclosure);
		}

		rewind($fp);
		$content=stream_get_contents($fp);
		$content=mb_convert_encoding($content,'UTF-16LE','UTF-8');
		$filename=$name.'_'.date('Y-m-d').'.csv';
		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Type: application/vnd.ms-excel; charset=UTF-16LE");
		if (ini_get("output_handler")=='') {
			header("Content-Length: ".(function_exists('mb_strlen') ? mb_strlen($content,'8bit') : strlen($content)));
		}
		header("Content-Transfer-Encoding: binary");
		header("Cache-Control: max-age=0");
		print chr(255).chr(254).$content;
		exit;
	}

	/**
	 * Download Excel.
	 *
	 * @param string $name Model name
	 */
	public function actionExcel($name)
	{
		$model=$this->module->loadModel($name);

		$memoryLimit=5*1024*1024;
		$begin='<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"><title>'.$name;
		$begin.='</title></head><body><table cellpadding="3" cellspacing="0" width="100%" border="1">';
		$end='</table></body></html>';
		$header='<tr>';
		$select='';

		foreach ($model->tableSchema->columns as $column) {
			// skip primary key?
			//if ($column->isPrimaryKey===true) continue;

			$header.='<th align="left" style="color: #ef4a2c;">'.(string)trim($model->getAttributeLabel($column->name)).'</th>';

			if ($select!='') {
				$select.=', ';
			}
			$select.=Yii::app()->db->quoteColumnName($column->name);
		}
		$header.='</tr>';
		$provider=Yii::app()->db->createCommand('SELECT '.$select.' FROM '.$model->tableSchema->name)->queryAll();

		// memory limit before php://temp starts using a temporary file
		$fp=fopen("php://temp/maxmemory:$memoryLimit",'w');

		fwrite($fp,$begin);

		// header line
		fwrite($fp,$header);

		// content lines
		foreach ($provider as $row) {
			$retVal='<tr>';
			foreach ($row as $item) {
				if ($item==0 || !empty($item)) {
					$retVal.='<td>'.(string)trim($item).'</td>';
				} else {
					$retVal.='<td>&nbsp;</td>';
				}
			}
			$retVal.='</tr>';
			fwrite($fp,$retVal);
		}

		fwrite($fp,$end);
		rewind($fp);
		$content=stream_get_contents($fp);
		$filename=$name.'_'.date('Y-m-d').'.xls';
		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
		if (ini_get("output_handler")=='') {
			header("Content-Length: ".(function_exists('mb_strlen') ? mb_strlen($content,'8bit') : strlen($content)));
		}
		header("Content-Transfer-Encoding: binary");
		header("Cache-Control: max-age=0");
		print $content;
		exit;
	}
}