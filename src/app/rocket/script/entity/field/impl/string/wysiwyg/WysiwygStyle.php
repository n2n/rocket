<?php
namespace rocket\script\entity\field\impl\string\wysiwyg;

class WysiwygStyle {
	private $name;
	private $element;
	private $attrs;
	private $styles;
	
	public function __construct($name, $element, array $attrs = null, array $styles = null) {
		$this->name = $name;
		$this->element = strval($element);
		$this->attrs = $attrs;
		$this->styles = $styles;
	}
	
	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getElement() {
		return $this->element;
	}

	public function setElement($element) {
		$this->element = $element;
	}

	public function getAttrs() {
		return (array) $this->attrs;
	}

	public function setAttrs(array $attrs) {
		$this->attrs = $atts;
	}

	public function getStyles() {
		return (array) $this->styles;
	}

	public function setStyles(array $styles) {
		$this->styles = $styles;
	}
	
	public function getValueForJsonEncode() {
		$ret = array('name' => $this->name, 'element' => $this->element);
		if (null != $this->attrs) {
			$ret['attributes'] = $this->attrs;
		}
		if (null != $this->styles) {
			$ret['styles'] = $this->styles;
		}
		return $ret;
	}
}
