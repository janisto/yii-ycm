<?php

class UserIdentity extends CUserIdentity
{
	/**
	 * Authenticates a user.
	 * @throws CException
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$password=Yii::app()->getModule('ycm')->password;
		if ($password===null) {
			throw new CException('Please configure the "password" property of the module.');
		} else if ($password===false || $password===$this->password) {
			$this->errorCode=self::ERROR_NONE;
		} else {
			$this->errorCode=self::ERROR_UNKNOWN_IDENTITY;
		}
		return !$this->errorCode;
	}
}