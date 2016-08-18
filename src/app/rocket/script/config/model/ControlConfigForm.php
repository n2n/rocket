<?php

namespace rocket\script\config\model;

use n2n\dispatch\Dispatchable;
use rocket\script\entity\EntityScript;
use rocket\script\core\ScriptManager;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\val\ValEnum;
use n2n\l10n\Locale;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\DispatchAnnotations;

class ControlConfigForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->annotateClass(DispatchAnnotations::MANAGED_PROPERTIES, 
				array('names' => array('partialControlOrder', 'overallControlOrder', 'entryControlOrder')));
		$as->annotateMethod('save', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $entityScript;
	private $scriptManager;
	private $partialControlOptions;
	private $overallControlOptions;
	private $entryControlOptions;
	
	public function __construct(EntityScript $entityScript, ScriptManager $scriptManager, Locale $locale) {
		$this->entityScript = $entityScript;
		$this->scriptManager = $scriptManager;
		$this->partialControlOptions = $entityScript->getPartialControlOptions($locale);
		$this->overallControlOptions = $entityScript->getOverallControlOptions($locale);
		$this->entryControlOptions = $entityScript->getEntryControlOptions($locale);
	}
	
	public function getPartialControlOptions() {
		return $this->partialControlOptions;
	}
	
	public function getOverallControlOptions() {
		return $this->overallControlOptions;
	}
	
	public function getEntryControlOptions() {
		return $this->entryControlOptions;
	}
	
	public function getEntityScript() {
		return $this->entityScript;
	}
	
	public function getPartialControlOrder() {
		return $this->entityScript->getPartialControlOrder();
	}
	
	public function setPartialControlOrder(array $partialButtonOrder) {
		$this->entityScript->setPartialControlOrder($partialButtonOrder);
	}
	
	public function getOverallControlOrder() {
		return $this->entityScript->getOverallControlOrder();
	}
	
	public function setOverallControlOrder(array $overallButtonOrder) {
		$this->entityScript->setOverallControlOrder($overallButtonOrder);
	}
	
	public function getEntryControlOrder() {
		return $this->entityScript->getEntryControlOrder();
	}
	
	public function setEntryControlOrder(array $entryButtonOrder) {
		$this->entityScript->setEntryControlOrder($entryButtonOrder);
	}
	
	private function _validation(BindingConstraints $bc) {
		$bc->val('partialControlOrder', new ValEnum(array_keys($this->partialControlOptions)));
		$bc->val('overallControlOrder', new ValEnum(array_keys($this->overallControlOptions)));
		$bc->val('entryControlOrder', new ValEnum(array_keys($this->entryControlOptions)));
	}
	
	public function save() {
		$this->scriptManager->persist();
	}
	
}