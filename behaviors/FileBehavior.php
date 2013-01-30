<?php

class FileBehavior extends CModelBehavior
{
	/**
	 * Get relative file URL.
	 *
	 * @param string $attribute
	 * @return string the file URL
	 */
	public function getFileUrl($attribute)
	{
		$name=get_class($this->owner);
		if ($this->owner->hasAttribute($attribute) && !empty($this->owner->$attribute)) {
			$file=$this->owner->$attribute;
			return Yii::app()->getModule('ycm')->getAttributeUrl($name,$attribute,$file);
		}
		return false;
	}

	/**
	 * Get absolute file URL.
	 *
	 * @param string $attribute
	 * @return string the absolute file URL
	 */
	public function getAbsoluteFileUrl($attribute)
	{
		$url=$this->getFileUrl($attribute);
		if ($url) {
			return Yii::app()->getRequest()->getHostInfo().$url;
		}
		return false;
	}
}