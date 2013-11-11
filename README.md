yii-ycm
=====================

YCM - Yii Content Management module

- [Documentation](http://janisto.github.com/yii-ycm/)
- [Examples](http://janisto.github.com/yii-ycm/)
- [Github Project Page](https://github.com/janisto/yii-ycm/)
- [Forum topic](http://www.yiiframework.com/forum/index.php/topic/37136-module-ycm-yii-content-management-module/)

See examples for all the options.

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

You can also use [composer](http://getcomposer.org/doc/).

- Require the package.

~~~
{
	"name": "app-name",
	"description": "App description",
	"type": "project",
	"prefer-stable": true,
	"require": {
		"php": ">=5.3.0",
		"yiisoft/yii": "1.1.14",
		"janisto/yii-ycm": "1.1.0",
	}
}
~~~

- Add vendor path to your configuration file, enable module, set username, password and models you want to manage.

~~~
	'aliases'=>array(
		'vendor'=>realpath(__DIR__ . '/../../vendor'),
	),
	'modules'=>array(
		...
		'ycm'=>array(
			'class' =>'vendor.janisto.yii-ycm.YcmModule',
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

Update
------------------

- Clear assets folder.

Changelog
------------------

### v1.1.1

- Fix time format.

### v1.1.0

- Add German translation.
- Fix: behaviour class path.

### v1.0.0

- Fix: override options in all form widgets.
- Add support for taggable behavior.
- Add Chinese translation.
- Update Finnish translation.
- Improve Google Analytics statistics page.
- Update libraries.
- Update Composer support.

### v0.5.0

- Google Analytics statistics page.
- Update yii-chosen to version v1.4.0
- Update Redactor to 8.2.6
- Composer support.
- Fix: Better url & path handling.
- Code cleanup.
- Update Finnish translation.

### v0.4.0

- Update yii-chosen to version v1.1.0
- Add first and last to pager.
- Fix: allow auto login.


### v0.3.0

- Bootstrap typehead support.
- Localization support and Finnish translation.
- Fix: loadModel doesn't require PHP 5.3+ anymore.

### v0.2.0

- Initial version.

License
------------------

yii-ycm is free and unencumbered [public domain][Unlicense] software.

[Unlicense]: http://unlicense.org/