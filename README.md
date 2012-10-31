yii-ycm
=====================

YCM - Yii Content Management module

Requirements
------------------

- Yii 1.1.10 or above (Requires jQuery 1.7.1)

Installation
------------------

- Download yii-ycm or clone the files to `protected/modules/ycm`
- Edit yii main configuration file `protected/config/main.php`
Enable module, set username, password and models you want to manage.
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

Usage
------------------


Changelog
------------------


License
------------------

yii-ycm is free and unencumbered [public domain][Unlicense] software.

[Unlicense]: http://unlicense.org/