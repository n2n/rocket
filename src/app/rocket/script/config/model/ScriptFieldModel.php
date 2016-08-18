<?php
namespace rocket\script\config\model;

use n2n\dispatch\val\ValIsset;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\DispatchAnnotations;
use n2n\dispatch\Dispatchable;
use n2n\dispatch\val\ValEnum;
use rocket\script\entity\field\ScriptField;
use n2n\reflection\annotation\AnnotationSet;

class ScriptFieldModel implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES,
				array('names' => array('className', 'label', 'orderIndex')));
	}

	private $scriptField;
	private $className;
	private $orderIndex;
	private $classNameOptions;

	public function __construct(ScriptField $scriptField, $orderIndex) {
		$this->scriptField = $scriptField;
		$this->orderIndex = $orderIndex;
		$this->className = get_class($scriptField);
	}

	public function getName() {
		return $this->scriptField->getId();
	}

	public function hasTypeChanged() {
		return isset($this->classNameOptions) && $this->className != get_class($this->scriptField);
	}

	public function getTypeName() {
		return $this->scriptField->getTypeName();
	}

	public function getScriptField() {
		return $this->scriptField;
	}

	public function getClassName() {
		return $this->className;
	}

	public function setClassName($className) {
		$this->className = $className;
	}

	public function setClassNameOptions(array $classNameOptions = null) {
		$this->classNameOptions = $classNameOptions;
	}

	public function getClassNameOptions() {
		return $this->classNameOptions;
	}

	public function getLabel() {
		return $this->scriptField->getLabel();
	}

	public function setLabel($label) {
		$this->scriptField->setLabel($label);
	}
	
	public function getOrderIndex() {
		return $this->orderIndex;
	}

	public function setOrderIndex($orderIndex) {
		$this->orderIndex = $orderIndex;
	}

	private function _validation(BindingConstraints $bc) {
		if (isset($this->classNameOptions)) {
			$bc->val('className', new ValEnum(array_keys($this->classNameOptions)));
		} else {
			$bc->ignore('className');
		}
		$bc->val('label', new ValIsset());
	}
}