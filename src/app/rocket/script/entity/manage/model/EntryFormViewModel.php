<?php
namespace rocket\script\entity\manage\model;


use rocket\script\entity\EntityScript;
use n2n\dispatch\PropertyPath;

class EntryFormViewModel {
	private $entryForm;
	private $basePropertyPath;
	
	public function __construct(EntryForm $entryForm, PropertyPath $basePropertyPath) {
		$this->entryForm = $entryForm;
		$this->basePropertyPath = $basePropertyPath;
	}
	
	public function getBasePropertyPath() {
		return $this->basePropertyPath;
	}
	
	public function isTypeChangable() {
		return $this->entryForm->getLevelEntryFormParts();
	}
	
	public function getTypeOptions() {
		return $this->entryForm->getTypeOptions();
	}
	
	public function createEditView() {
		$mainEntryFormPart = $this->entryForm->getMainEntryFormPart();
		return $mainEntryFormPart->getDisplayDefinition()->getScriptMask()
				->createEditEntryView($mainEntryFormPart, $this->basePropertyPath->ext('mainEntryFormPart'));
	}
	
	public function getTypeLevelIds() {
		$typeLevelIds = array();
		foreach ($this->entryForm->getLevelEntryFormParts() as $entityScriptId => $levelEntryModel) {
			$entityScript = $levelEntryModel->getDisplayDefinition()->getEntityScript();
			
			$typeLevelIds[$entityScriptId] = $this->buildTypeHtmlClasses($entityScript, array()); 
		}
		return $typeLevelIds;
	}
	
	private function buildTypeHtmlClasses(EntityScript $entityScript, array $htmlClasses) {
		$htmlClasses[] = 'rocket-script-type-' . $entityScript->getId();
		foreach ($entityScript->getSubEntityScripts() as $subEntityScript) {
			$htmlClasses = $this->buildTypeHtmlClasses($subEntityScript, $htmlClasses);
		}
		return $htmlClasses;
	}
	
	public function createTypeLevelEditView($entityScriptId) {
		$entryFormParts = $this->entryForm->getLevelEntryFormParts();
		if (!isset($entryFormParts[$entityScriptId])) {
			throw new \InvalidArgumentException();
		}
		
		return $entryFormParts[$entityScriptId]->getDisplayDefinition()->getScriptMask()
				->createEditEntryView($entryFormParts[$entityScriptId], 
						$this->basePropertyPath->ext('levelEntryFormParts')->fieldExt($entityScriptId));
	}
}