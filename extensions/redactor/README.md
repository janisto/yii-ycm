yii-redactor
=====================

Yii Widget for Redactor - Fantastic WYSIWYG editor on jQuery

- [Examples](http://imperavi.com/redactor/examples/)
- [Documentation](http://imperavi.com/redactor/docs/)
- [Download Redactor](http://imperavi.com/redactor/download/)
- [Download languages](http://imperavi.com/redactor/docs/languages/)
- [Github Project Page](https://github.com/janisto/yii-redactor)

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

### Override default settings in config/main.php

~~~
return array(

	// application components
	'components'=>array(
		(...)

		// Defaults to Widgets
		'widgetFactory' => array(
			'widgets' => array(
				'ERedactorWidget' => array(
					'options'=>array(
						'lang'=>'fi',
						'buttons'=>array(
							'formatting', '|', 'bold', 'italic', 'deleted', '|',
							'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
							'image', 'video', 'link', '|', 'html',
						),
					),
				),
			),
		),

		(...)
	),
);
~~~

### Image and file upload with default actions

#### Step 1

Let's assume we are using Post model and PostController.

Create "uploads" folder to application root, add write permissions to it and add actions to PostController with default values.

~~~
class PostController extends Controller
{
	public function actions()
	{
		return array(
			'fileUpload'=>'ext.redactor.actions.FileUpload',
			'imageUpload'=>'ext.redactor.actions.ImageUpload',
			'imageList'=>'ext.redactor.actions.ImageList',
		);
	}
	...
}
~~~

Or let actions create "uploads" folder automatically to application root folder.

~~~
class PostController extends Controller
{
	public function actions()
	{
		return array(
			'fileUpload'=>array(
				'class'=>'ext.redactor.actions.FileUpload',
				'uploadCreate'=>true,
			),
			'imageUpload'=>array(
				'class'=>'ext.redactor.actions.ImageUpload',
				'uploadCreate'=>true,
			),
			'imageList'=>array(
				'class'=>'ext.redactor.actions.ImageList',
			),
		);
	}
	...
}
~~~

Or add actions to PostController with other custom values.

~~~
class PostController extends Controller
{
	public function actions()
	{
		return array(
			'fileUpload'=>array(
				'class'=>'ext.redactor.actions.FileUpload',
				'uploadPath'=>'/path/to/uploads/folder',
				'uploadUrl'=>'/url/to/uploads/folder',
				'uploadCreate'=>true,
				'permissions'=>0755,
			),
			'imageUpload'=>array(
				'class'=>'ext.redactor.actions.ImageUpload',
				'uploadPath'=>'/path/to/uploads/folder',
				'uploadUrl'=>'/url/to/uploads/folder',
				'uploadCreate'=>true,
				'permissions'=>0755,
			),
			'imageList'=>array(
				'class'=>'ext.redactor.actions.ImageList',
				'uploadPath'=>'/path/to/uploads/folder',
				'uploadUrl'=>'/url/to/uploads/folder',
			),
		);
	}
	...
}
~~~

#### Step 2

Add widget to the form view.

~~~
$attribute='content';
$this->widget('ext.redactor.ERedactorWidget',array(
	'model'=>$model,
	'attribute'=>$attribute,
	'options'=>array(
		'fileUpload'=>Yii::app()->createUrl('post/fileUpload',array(
			'attr'=>$attribute
		)),
		'fileUploadErrorCallback'=>new CJavaScriptExpression(
			'function(obj,json) { alert(json.error); }'
		),
		'imageUpload'=>Yii::app()->createUrl('post/imageUpload',array(
			'attr'=>$attribute
		)),
		'imageGetJson'=>Yii::app()->createUrl('post/imageList',array(
			'attr'=>$attribute
		)),
		'imageUploadErrorCallback'=>new CJavaScriptExpression(
			'function(obj,json) { alert(json.error); }'
		),
	),
));
~~~

Changelog
------------------

### v1.2.0

- Update readme.

### v1.1.0

- Default actions for image and file upload.

### v1.0.0

- Initial version.

License
------------------

yii-redactor is free and unencumbered [public domain][Unlicense] software.

[Unlicense]: http://unlicense.org/
