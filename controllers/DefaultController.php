<?php

class DefaultController extends AdminController
{
	/**
	 * Displays a list of all models.
	 */
	public function actionIndex()
	{
		$this->render('index',array(
			'title'=>YcmModule::t('Administration'),
			'models'=>$this->module->modelsList,
		));
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error) {
			if(Yii::app()->request->isAjaxRequest) {
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
		// collect user input data
		if(isset($_POST['LoginForm'])) {
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to module home page.
			if($model->validate() && $model->login()) {
				$this->redirect(Yii::app()->createUrl($this->module->name));
			}
		}
		// display the login form
		$this->render('login',array('model'=>$model,'title'=>YcmModule::t('Login')));
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