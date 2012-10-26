<?php

class ModelController extends AdminController
{
	/**
	 * @var string Default action name
	 */
	public $defaultAction = 'list';

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
			foreach ($params as $key => $val) {
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
				$columnName=$column->name;
				$widget=$this->module->getAttributeWidget($columnName);
				if($widget=='file' || $widget=='image') { // file or image
					$file=CUploadedFile::getInstance($model,$columnName);
					if (is_object($file) && get_class($file)==='CUploadedFile') {
						$fileName=hash('sha256',uniqid(rand(),true)).'.'.$file->getExtensionName();
						$columnPath=$this->module->uploadPath.DIRECTORY_SEPARATOR.strtolower($name).DIRECTORY_SEPARATOR.$columnName;
						if(!is_dir($columnPath)) {
							if (!mkdir($columnPath,0644,true)) { // Read and write for owner, read for everybody else
								throw new CHttpException(500,'Could not create folder: '.$columnPath.'. Make sure "uploads" folder is writable.');
							}
						}
						$path=$columnPath.DIRECTORY_SEPARATOR.$fileName;
						if(file_exists($path) || !$file->saveAs($path)) {
							throw new CHttpException(500,'Could not save file or file exists: '.$path);
						}
						array_push($paths,$path);
						$model->$columnName=$fileName;
					}
				}
			}

            if ($model->save()) {
                Yii::app()->user->setFlash('success',YcmModule::t('Changes saved.'));
                $this->redirectUser($name,$model->primaryKey);
            } else if (count($paths)!=0) {
				foreach($paths as $path) {
					if(file_exists($path)) {
						@unlink($path); // Save failed - delete files.
					}
				}
			}
        }

        $title=YcmModule::t('Create').' '.$this->module->getSingularName($model);
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
		$model=$this->module->loadModel($name)->findByPk($pk);

        if (Yii::app()->request->isPostRequest && isset($_POST[$name])) {
			$paths=array();
			$oldPaths=array();
			$deleteOld=array();

			// store old files to array
			foreach ($model->tableSchema->columns as $column) {
				$columnName=$column->name;
				$widget=$this->module->getAttributeWidget($columnName);
				if($widget=='file' || $widget=='image') { // file or image
					$columnPath=$this->module->uploadPath.DIRECTORY_SEPARATOR.strtolower($name).DIRECTORY_SEPARATOR.$columnName;
					if(isset($model->$columnName)) {
						$path=$columnPath.DIRECTORY_SEPARATOR.$model->$columnName;
						$oldPaths[$columnName]=$path;
					}
				}
			}

			// set attributes from POST
			$model->attributes=$_POST[$name];

			foreach ($model->tableSchema->columns as $column) {
				$columnName=$column->name;
				$widget=$this->module->getAttributeWidget($columnName);
				if($widget=='file' || $widget=='image') { // file or image
					$file=CUploadedFile::getInstance($model,$columnName);
					if (is_object($file) && get_class($file)==='CUploadedFile') {
						$fileName=hash('sha256',uniqid(rand(),true)).'.'.$file->getExtensionName();
						$columnPath=$this->module->uploadPath.DIRECTORY_SEPARATOR.strtolower($name).DIRECTORY_SEPARATOR.$columnName;
						if(!is_dir($columnPath)) {
							if (!mkdir($columnPath,0644,true)) { // Read and write for owner, read for everybody else
								throw new CHttpException(500,'Could not create folder: '.$columnPath.'. Make sure "uploads" folder is writable.');
							}
						}
						$path=$columnPath.DIRECTORY_SEPARATOR.$fileName;
						if(file_exists($path) || !$file->saveAs($path)) {
							throw new CHttpException(500,'Could not save file or file exists: '.$path);
						}
						array_push($paths,$path);
						array_push($deleteOld,$columnName);
						$model->$columnName=$fileName;
					}
				}
			}

            if ($model->save()) {
				if (count($deleteOld)!=0) {
					foreach($deleteOld as $old) {
						if(isset($oldPaths[$old]) && file_exists($oldPaths[$old])) {
							@unlink($oldPaths[$old]);
						}
					}
				}
                Yii::app()->user->setFlash('success',YcmModule::t('Changes saved.'));
                $this->redirectUser($name,$model->primaryKey);
			} else if (count($paths)!=0) {
				foreach($paths as $path) {
					if(file_exists($path)) {
						@unlink($path); // Save failed - delete files.
					}
				}
			}
        }

        $title=YcmModule::t('Edit').' '.$this->module->getSingularName($model);
        $this->breadcrumbs=array(
			$this->module->getAdminName($model)=>$this->createUrl('model/list',array('name'=>$name)),
			$title,
        );

        $this->render('form',array(
            'title'=>YcmModule::t('Edit').' '.$this->module->getSingularName($model),
            'model'=>$model,            
        ));
    }
	/**
	 * Delete model.
	 *
	 * @param string $name Model name
	 * @param integer $pk Primary key
	 */
    public function actionDelete($name,$pk)
    {
		$model=$this->module->loadModel($name)->findByPk($pk);

        if ($model!==null) {
            $model->delete();

			// delete files
			foreach ($model->tableSchema->columns as $column) {
				$columnName=$column->name;
				$widget=$this->module->getAttributeWidget($columnName);
				if($widget=='file' || $widget=='image') { // file or image
					$columnPath=$this->module->uploadPath.DIRECTORY_SEPARATOR.strtolower($name).DIRECTORY_SEPARATOR.$columnName;
					if(isset($model->$columnName)) {
						$path=$columnPath.DIRECTORY_SEPARATOR.$model->$columnName;
						if(file_exists($path)) {
							@unlink($path); // Delete files.
						}
					}
				}
			}

            $this->redirect($this->createUrl('model/list',array('name'=>$name)));
        }
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
            Yii::app()->user->setFlash('success',YcmModule::t('Changes saved. You can add a new entry.'));
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

		foreach($model->tableSchema->columns as $column) {
			// skip primary key?
            //if ($column->isPrimaryKey===true) continue;

			// no new lines in CSV format.
			$header[]=(string)str_replace(array("\r","\r\n","\n"),'',trim($model->getAttributeLabel($column->name)));

			if($select!='') {
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
		foreach($provider as $row) {
			$retVal=array();
			foreach($row as $item) {
				if($item==0 || !empty($item)) {
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
		$header = array();
		$select = '';

		foreach($model->tableSchema->columns as $column) {
			// skip primary key?
            //if($column->isPrimaryKey===true) continue;

			// no new lines in CSV format.
			$header[]=(string)str_replace(array("\r","\r\n","\n"),'',trim($model->getAttributeLabel($column->name)));

			if($select!='') {
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
		foreach($provider as $row) {
			$retVal=array();
			foreach($row as $item) {
				if($item==0 || !empty($item)) {
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
		if(ini_get("output_handler")=='') {
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

		foreach($model->tableSchema->columns as $column) {
			// skip primary key?
            //if($column->isPrimaryKey===true) continue;

			$header.='<th align="left" style="color: #ef4a2c;">'.(string)trim($model->getAttributeLabel($column->name)).'</th>';

			if($select!='') {
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
		foreach($provider as $row) {
			$retVal='<tr>';
			foreach($row as $item) {
				if($item==0 || !empty($item)) {
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
		if(ini_get("output_handler")=='') {
			header("Content-Length: ".(function_exists('mb_strlen') ? mb_strlen($content,'8bit') : strlen($content)));
		}
		header("Content-Transfer-Encoding: binary");
		header("Cache-Control: max-age=0");
		print $content;

		exit;
	}
}
