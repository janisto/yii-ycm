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
		$model=strtolower(get_class($this->getOwner()));
		if ($this->owner->hasAttribute($attribute) && !empty($this->getOwner()->$attribute)) {
			$image=$this->getOwner()->$attribute;
			return Yii::app()->getModule('ycm')->uploadUrl."/$model/$attribute/$image";
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
		if($url) {
			return Yii::app()->getRequest()->getHostInfo().$url;
		}
		return false;
	}
}