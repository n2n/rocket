<?php

namespace rocket\script\entity\filter\data;

use n2n\util\Attributes;
use n2n\reflection\ArgumentUtils;

class FilterData {
	protected $elements = array();
		
	public function clear() {
		$this->attributes = array();
	}
	
	public function getElements() {
		return $this->elements;
	}
	
	public function addElement(FilterDataElement $element) {
		$this->elements[] = $element;
	}
	
	public function isEmpty() {
		return empty($this->elements);
	}
	
	public function setElements(array $elements) {
		ArgumentUtils::validateArrayType($elements, 'rocket\script\entity\filter\data\FilterDataElement');
		$this->elements = $elements;
	}
	
	public function toArray() {
		$attrs = array();
		foreach ($this->elements as $element) {
			$attrs[] = $element->toArray();
		}
		return $attrs;
	}

	public static function createFromArray(array $array) {
		$filterData = new FilterData();
		foreach ($array as $attrs) {
			try {
				$filterData->elements[] = FilterDataElement::create($attrs);
			} catch (\InvalidArgumentException $e) { }
		}
		return $filterData;
	}
	
	
}

abstract class FilterDataElement {
	const TYPE_KEY = 'type';
	const TYPE_USAGE = 'usage';
	const TYPE_GROUP = 'group';
	
	public static function create(array $attrs) {
		if (isset($attrs[self::TYPE_KEY])) {
			if ($attrs[self::TYPE_KEY] == self::TYPE_USAGE) {
				return FilterDataUsage::create($attrs);
			}

			if ($attrs[self::TYPE_KEY] == self::TYPE_GROUP) {
				return FilterDataGroup::create($attrs);
			}
		}

		throw new \InvalidArgumentException();
	}
	
	public abstract function toArray();
}

class FilterDataUsage extends FilterDataElement {
	const ATTR_ITEM_ID_KEY = 'itemId';
	const ATTR_ATTRS_KEY = 'attrs';
	
	
	private $itemId;
	private $attributes;
	
	public function __construct($itemId, Attributes $attributes) {
		$this->itemId = $itemId;
		$this->attributes = $attributes;
	}
	
	public function setItemId($itemId) {
		$this->itemId = $itemId;
	}
	
	public function getItemId() {
		return $this->itemId;
	}
	
	public function setAttributes(Attributes $attributes) {
		$this->attributes = $attributes;
	}
	
	public function getAttributes() {
		return $this->attributes;
	}
	
	public function toArray() {
		return array(self::TYPE_KEY => self::TYPE_USAGE,
				self::ATTR_ITEM_ID_KEY => $this->itemId,
				self::ATTR_ATTRS_KEY => $this->attributes->toArray());
		
	}
	
	public static function create(array $attrs) {
		if (!isset($attrs[self::ATTR_ITEM_ID_KEY]) || !isset($attrs[self::ATTR_ATTRS_KEY])
				|| !is_array($attrs[self::ATTR_ATTRS_KEY])) {
			throw new \InvalidArgumentException();
		}
		
		return new FilterDataUsage($attrs[self::ATTR_ITEM_ID_KEY], 
				new Attributes($attrs[self::ATTR_ATTRS_KEY]));
	}
}

class FilterDataGroup extends FilterDataElement {
	const ATTR_ELEMENTS_KEY = 'elements';
	const ATTR_USE_AND_KEY = 'useAnd';
	
	private $elements;
	private $andUsed;
	
	public function getAll() {
		return $this->elements;
	}
	
	public function setAll(array $elements) {
		$this->clear();
		foreach ($elements as $element) {
			$this->add($element);
		}
	}
	
	public function add(FilterDataElement $element) {
		$this->elements[] = $element;	
	}
	
	public function isAndUsed() {
		return $this->andUsed;
	}
	
	public function setAndUsed($andUsed) {
		$this->andUsed = (boolean) $andUsed;
	}
	
	public function toArray() {
		$elementAttrs = array();
		foreach ($this->elements as $element) {
			$elementAttrs[] = $element->toArray();
		}
		
		return array(self::TYPE_KEY => self::TYPE_GROUP,
				self::ATTR_USE_AND_KEY => $this->andUsed,
				self::ATTR_ELEMENTS_KEY => $elementAttrs);
	}
	
	public static function create(array $attrs) {
		$fdg = new FilterDataGroup();
		$fdg->setAndUsed(isset($attrs[self::ATTR_USE_AND_KEY]) && $attrs[self::ATTR_USE_AND_KEY]);
		
		if (!isset($attrs[self::ATTR_ELEMENTS_KEY]) || !is_array($attrs[self::ATTR_ELEMENTS_KEY])) {
			throw new \InvalidArgumentException();
		}

		foreach ($attrs[self::ATTR_ELEMENTS_KEY] as $elementAttrs) {
			if (!is_array($elementAttrs)) {
				throw new \InvalidArgumentException();
			}
			$fdg->add(FilterDataElement::create($elementAttrs));
		}
		
		return $fdg;
	}
}