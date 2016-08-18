<?php
namespace rocket\script\core\extr;

class ScriptFieldExtraction extends ScriptElementExtraction {
	private $label;
	private $propertyName;
	private $entityPropertyName;

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}

	public function getPropertyName() {
		return $this->propertyName;
	}

	public function setPropertyName($propertyName) {
		$this->propertyName = $propertyName;
	}

	public function getEntityPropertyName() {
		return $this->entityPropertyName;
	}

	public function setEntityPropertyName($entityPropertyName) {
		$this->entityPropertyName = $entityPropertyName;
	}
}