yii-chosen
=====================

Yii Widget for Chosen - Chosen is a JavaScript plugin that makes long, unwieldy select boxes much more user-friendly.

- [Documentation](http://harvesthq.github.com/chosen/)

Requirements
------------------

- Yii 1.1 or above (Chosen requires jQuery 1.4)

Installation
------------------

1. Download yii-chosen or Clone the files
2. Extract into the widgets or extensions folder

Usage
------------------

### Common usage inside a view

~~~
$this->widget('ext.chosen.EChosenWidget');
~~~

### Using with a jQuery selector

~~~
$this->widget('ext.chosen.EChosenWidget',array(
	// the select selector
	'selector'=>'.chosen',
	// Chosen options
	'options'=>array(),
));
~~~

Changelog
------------------

### v1.6.1

- Chosen updated to version 1.2.0.

### v1.6.0

- Chosen updated to version 1.1.0.

### v1.5.0

- Chosen updated to version 1.0.0.
- Default selector for jQuery change to ".chosen-select".

### v1.4.0

- Chosen updated to version 0.9.13.

### v1.3.0

- Chosen updated to version 0.9.12.
- Composer support.

### v1.2.0

- Use CWidget instead of CInputWidget.

### v1.1.0

- Chosen updated to version 0.9.10.

### v1.0.0

- Initial version.

License
------------------

yii-chosen is free and unencumbered [public domain][Unlicense] software.

[Unlicense]: http://unlicense.org/