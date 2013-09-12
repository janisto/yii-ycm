<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping  user login form data.
 * It is used by the 'login' action of 'DefaultController'.
 */
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe;
	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			array('username, password', 'required'),
			array('rememberMe', 'boolean'),
			array('password', 'authenticate'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'username'=>Yii::t('YcmModule.ycm','Username'),
			'password'=>Yii::t('YcmModule.ycm','Password'),
			'rememberMe'=>Yii::t('YcmModule.ycm','Remember me next time'),
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */ 
	public function authenticate($attribute,$params)
	{
		if (!$this->hasErrors()) {
			$this->_identity=new UserIdentity($this->username,$this->password);
			if (!$this->_identity->authenticate()) {
				$this->addError('password',Yii::t('YcmModule.ycm','Incorrect username or password.'));
			}
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
		if ($this->_identity===null) {
			$this->_identity=new UserIdentity($this->username,$this->password);
			$this->_identity->authenticate();
		}
		if ($this->_identity->errorCode===UserIdentity::ERROR_NONE) {
			$duration=$this->rememberMe ? 3600*24*30 : 300; // 30 days
			Yii::app()->user->login($this->_identity,$duration);
			return true;
		} else {
			return false;
		}
	}
}