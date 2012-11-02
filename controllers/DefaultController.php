<?php

class DefaultController extends AdminController
{
	/**
	 * Displays a list of all models.
	 */
	public function actionIndex()
	{
		$this->render('index',array(
			'title'=>Yii::t('YcmModule.ycm','Administration'),
			'models'=>$this->module->modelsList,
		));
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if ($error=Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest) {
				echo $error['message'];
			} else {
				$this->render('error', $error);
			}
		}
	}

	/**
	 * Displays the login page.
	 */
	public function actionLogin()
	{
		$model=Yii::createComponent('LoginForm');
		if (isset($_POST['LoginForm'])) {
			$model->attributes=$_POST['LoginForm'];
			if ($model->validate() && $model->login()) {
				$this->redirect(Yii::app()->createUrl($this->module->name));
			}
		}
		$this->render('login',array('model'=>$model,'title'=>Yii::t('YcmModule.ycm','Login')));
	}

	/**
	 * Logs out the current user and redirects to home page.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout(false);
		$this->redirect(Yii::app()->createUrl($this->module->name));
	}
}