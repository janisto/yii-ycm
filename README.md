yii-ycm
=====================

YCM - Yii Content Management module

- [Documentation](http://janisto.github.com/yii-ycm/)
- [Examples](http://janisto.github.com/yii-ycm/)
- [Github Project Page](https://github.com/janisto/yii-ycm/)
- [Forum topic](http://www.yiiframework.com/forum/index.php/topic/37136-module-ycm-yii-content-management-module/)

Requirements
------------------

- Yii 1.1.10 or above (Requires jQuery 1.7.1)

Installation
------------------

- Download yii-ycm or clone the files to `protected/modules/ycm`
- Edit Yii main configuration file `protected/config/main.php`. Enable module, set username, password and models you want to manage.

~~~
	'modules'=>array(
		...
		'ycm'=>array(
			'username'=>'YOUR USERNAME',
			'password'=>'YOUR PASSWORD',
			'registerModels'=>array(
				//'application.models.Blog', // one model
				'application.models.*', // all models in folder
			),
			'uploadCreate'=>true, // create upload folder automatically
			'redactorUpload'=>true, // enable Redactor image upload
		),
		...
	),
~~~

Changelog
------------------

### v0.4

- Update yii-chosen to version v1.1.
- Add first and last to pager.
- Fix: allow auto login.


### v0.3

- Bootstrap typehead support.
- Localization support and Finnish translation.
- Fix: loadModel doesn't require PHP 5.3+ anymore.

### v0.2

- Initial version.

License
------------------

yii-ycm is free and unencumbered [public domain][Unlicense] software.

[Unlicense]: http://unlicense.org/