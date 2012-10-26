yii-redactor
=====================

Yii Widget for Redactor - Fantastic WYSIWYG editor on jQuery

- [Examples](http://redactorjs.com/examples/)
- [Documentation](http://redactorjs.com/docs/)
- [Download Redactor](http://redactorjs.com/download/)
- [Download languages](http://redactorjs.com/docs/languages/)

Requirements
------------------

- Redactor 8.0 or above
- Yii 1.1.10 or above (Redactor requires jQuery 1.7.1)

Installation
------------------

1. Download yii-redactor or Clone the files
2. Extract into the widgets or extensions folder
3. Download Redactor and language files if needed
4. Extract Redactor files
 - redactor.css -> redactor/assets/css/
 - redactor.js and redactor.min.js -> redactor/assets/js/
 - language files -> redactor/assets/js/lang/

Usage
------------------

### Using with a model

~~~
$this->widget('ext.redactor.ERedactorWidget',array(
	'model'=>$model,
	'attribute'=>'some_attribute',
));
~~~

### Using with a model and a different language

~~~
$this->widget('ext.redactor.ERedactorWidget',array(
	'model'=>$model,
	'attribute'=>'some_attribute',
	// Redactor options
	'options'=>array(
		'lang'=>'fi',
	),
));
~~~

### Using with a name and value

~~~
$this->widget('ext.redactor.ERedactorWidget',array(
	'name'=>'some_name',
	'value'=>'some_value',
	// Redactor options
	'options'=>array(),
));
~~~

### Using with a jQuery selector

~~~
$this->widget('ext.redactor.ERedactorWidget',array(
	// the textarea selector
	'selector'=>'.redactor',
	// Redactor options
	'options'=>array(),
));
~~~

Changelog
------------------

### v1.0

- Initial version.

License
------------------

yii-redactor is free and unencumbered [public domain][Unlicense] software.

[Unlicense]: http://unlicense.org/