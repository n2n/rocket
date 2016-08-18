<?php
namespace rocket\script\config\model;

use n2n\dispatch\val\ValIsset;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\option\OptionCollectionDispatchable;
use n2n\dispatch\DispatchAnnotations;
use n2n\dispatch\Dispatchable;
use n2n\dispatch\val\ValEnum;
use n2n\reflection\annotation\AnnotationSet;
use rocket\script\entity\modificator\IndependentScriptModificator;

class ScriptModificatorModel implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES,
				array('names' => array('className', 'label', 'readOnly', 'optional', 'translation', 
						'draftEnabled',	'displayInListViewEnabled',	'displayInDetailViewEnabled', 
						'displayInEditViewEnabled',	'orderIndex', 'optionForm')));
	}

	private $scriptModificator;
	private $optionForm;

	public function __construct(IndependentScriptModificator $scriptModificator) {
		$this->scriptModificator = $scriptModificator;
	}
	
	public function getTypeName() {
		return $this->scriptField->getTypeName();
	}

	public function setOptionCollectionDispatchable(OptionCollectionDispatchable $optionForm) {
		$this->optionForm = $optionForm;
	}
	
	public function getOptionCollectionDispatchable() {
		return $this->optionForm;
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