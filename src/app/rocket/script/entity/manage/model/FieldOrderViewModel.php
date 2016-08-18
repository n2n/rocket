<?php
namespace rocket\script\entity\manage\model;

use rocket\script\entity\mask\GroupedFieldOrder;
use n2n\core\UnsupportedOperationException;
class FieldOrderViewModel {
	private $fieldOrder;
	private $current;
	
	public function __construct(array $fieldOrder) {
		$this->fieldOrder = $fieldOrder;
	}
	
	public function next() {
		if ($this->current === null) {
			if (false === reset($this->fieldOrder)) return false;
		} else {
			if (false === next($this->fieldOrder)) return false;
		}
		$this->current = current($this->fieldOrder);
		return true;
	}
	
	public function reset() {
		$this->current = null;
	}
	
	public function isGroup() {
		return $this->current instanceof GroupedFieldOrder;
	}
	
	public function containsAsideGroup() {
		foreach ($this->fieldOrder as $field) {
			if ($field instanceof GroupedFieldOrder && $field->getType() == GroupedFieldOrder::ASIDE) {
				return true;
			}
		}
		
		return false;
	}
	
	private function ensureCurrentIsGroup() {
		if ($this->isGroup()) return;
		
		throw new UnsupportedOperationException('Current is no group.');
	}
	
	public function getGroupTitle() {
		$this->ensureCurrentIsGroup();
		
		return $this->current->getTitle();
	}
	
	public function getGroupCssClassName() {
		$this->ensureCurrentIsGroup();
		
		if (null !== ($type = $this->current->getType())) {
			return 'rocket-control-group-' . $type;
		}
		
		return 'rocket-control-group';
	}
	
	public function getGroupFieldOrderModel() {
		$this->ensureCurrentIsGroup();
		
		return new FieldOrderViewModel($this->current->getFieldOrder());
	}
	
	public function getFieldId() {
		if ($this->current === null || $this->isGroup()) {
			throw new UnsupportedOperationException('Current is no field.');
		}
		
		return $this->current;
	}
}