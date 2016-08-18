<?php
namespace rocket\script\entity\mask;

class GroupedFieldOrder {
	const MAIN = 'main';
	const ASIDE = 'aside';
	
	private $type;
	private $title;
	private $fieldOrder = array();
	
	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getFieldOrder() {
		return $this->fieldOrder;
	}

	public function setFieldOrder(array $fieldOrder) {
		$this->fieldOrder = $fieldOrder;
	}
	
	public function add($fieldOrderKey) {
		$this->fieldOrder[] = $fieldOrderKey;
	}
	
	public function size() {
		return sizeof($this->fieldOrder);
	}
	
	public function copy(array $fieldOrder = null) {
		$copy = new GroupedFieldOrder();
		$copy->setTitle($this->getTitle());
		$copy->setType($this->getType());
		
		if ($fieldOrder !== null) {
			$copy->setFieldOrder($fieldOrder);
		} else {
			$copy->setFieldOrder($this->getFieldOrder());
		}
		
		return $copy;
	}
}