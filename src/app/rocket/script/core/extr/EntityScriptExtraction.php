<?php
namespace rocket\script\core\extr;

use rocket\script\core\ScriptManager;
use rocket\script\entity\EntityScript;
use rocket\script\entity\field\PropertyScriptField;
use rocket\script\entity\field\EntityPropertyScriptField;
use rocket\script\entity\IndependentScriptElement;
class EntityScriptExtraction extends ScriptExtraction {
	private $pluralLabel;
	private $entityClassName;
	private $dataSourceName;
	private $typeChangeMode = EntityScript::TYPE_CHANGE_MODE_DISABLED;
	private $draftHistorySize;
	private $knownStringPattern;
	private $previewControllerClassName;
	private $defaultSortDirections = array();
	private $fieldExtractions = array();
	private $commandExtractions = array();
	private $listenerExtractions = array();
	private $constraintExtractions = array();
// 	private $partialControlOrder = array();
// 	private $overallControlOrder = array();
// 	private $entryControlOrder = array();
	private $defaultMaskId;
	private $maskExtractions = array();
	
	public function getPluralLabel() {
		return $this->pluralLabel;
	}

	public function setPluralLabel($pluralLabel) {
		$this->pluralLabel = $pluralLabel;
	}

	public function getTypeChangeMode() {
		return $this->typeChangeMode;
	}
	
	public function setTypeChangeMode($typeChangeMode) {
		$this->typeChangeMode = $typeChangeMode;
	}

	public function getDataSourceName() {
		return $this->dataSourceName;
	}

	public function setDataSourceName($dataSourceName) {
		$this->dataSourceName = $dataSourceName;
	}

	public function getEntityClassName() {
		return $this->entityClassName;
	}

	public function setEntityClassName($entityClassName) {
		$this->entityClassName = $entityClassName;
	}

	public function getKnownStringPattern() {
		return $this->knownStringPattern;
	}

	public function setKnownStringPattern($knownStringPattern) {
		$this->knownStringPattern = $knownStringPattern;
	}
	
// 	public function isDraftDisabled() {
// 		return $this->draftDisabled;
// 	}

// 	public function setDraftDisabled($draftDisabled) {
// 		$this->draftDisabled = $draftDisabled;
// 	}

// 	public function isTranslationDisabled() {
// 		return $this->translationDisabled;
// 	}

// 	public function setTranslationDisabled($translationDisabled) {
// 		$this->translationDisabled = $translationDisabled;
// 	}

	public function getDraftHistorySize() {
		return $this->draftHistorySize;
	}

	public function setDraftHistorySize($draftHistorySize) {
		$this->draftHistorySize = $draftHistorySize;
	}

	public function getPreviewControllerClassName() {
		return $this->previewControllerClassName;
	}

	public function setPreviewControllerClassName($previewControllerClassName) {
		$this->previewControllerClassName = $previewControllerClassName;
	}

	public function getDefaultSortDirections() {
		return $this->defaultSortDirections;
	}

	public function setDefaultSortDirections(array $defaultSortDirections) {
		$this->defaultSortDirections = $defaultSortDirections;
	}

	public function getFieldExtractions() {
		return $this->fieldExtractions;
	}

	public function putFieldExtraction(ScriptFieldExtraction $scriptFieldExtraction) {
		$this->fieldExtractions[] = $scriptFieldExtraction;
	}

	public function removeFieldExtractions() {
		$this->fieldExtractions = array();
	}

	public function getCommandExtractions() {
		return $this->commandExtractions;
	}

	public function putCommandExtraction(ScriptElementExtraction $configurableExtraction) {
		$this->commandExtractions[] = $configurableExtraction;
	}

	public function removeCommandExtractions() {
		$this->commandExtractions = array();
	}

	public function getConstraintExtractions() {
		return $this->constraintExtractions;
	}

	public function putConstraintExtraction(ScriptElementExtraction $configurableExtraction) {
		$this->constraintExtractions[] = $configurableExtraction;
	}
	
	public function removeConstraintExtractions() {
		$this->constraintExtractions = array();
	}

	public function getListenerExtractions() {
		return $this->listenerExtractions;
	}

	public function putListenerExtraction(ScriptElementExtraction $configurableExtraction) {
		$this->listenerExtractions[] = $configurableExtraction;
	}
	
	public function removeListenerExtractions() {
		$this->listenerExtractions = array();
	}
	
// 	public function getPartialControlOrder() {
// 		return $this->partialControlOrder;
// 	}

// 	public function setPartialControlOrder(array $partialControlOrder) {
// 		$this->partialControlOrder = $partialControlOrder;
// 	}

// 	public function getOverallControlOrder() {
// 		return $this->overallControlOrder;
// 	}

// 	public function setOverallControlOrder(array $overallControlOrder) {
// 		$this->overallControlOrder = $overallControlOrder;
// 	}

// 	public function getEntryControlOrder() {
// 		return $this->entryControlOrder;
// 	}

// 	public function setEntryControlOrder(array $entryControlOrder) {
// 		$this->entryControlOrder = $entryControlOrder;
// 	}
	
	public function getDefaultMaskId() {
		return $this->defaultMaskId;
	}
	
	public function setDefaultMaskId($defaultMaskId) {
		$this->defaultMaskId = $defaultMaskId;
	}
	
	public function addMaskExtraction(ScriptMaskExtraction $maskExtraction) {
		$this->maskExtractions[$maskExtraction->getId()] = $maskExtraction;
	}
	
	public function removeMaskExtractions() {
		$this->maskExtractions = array();
	}
	/**
	 * @return \rocket\script\core\extr\ScriptMaskExtraction[]
	 */
	public function getMaskExtractions() {
		return $this->maskExtractions;
	}
	
	public function createScript(ScriptManager $scriptManager) {
		$factory = new EntityScriptFactory($scriptManager->getEntityModelManager());
		return $factory->create($this);
	}
	
	public static function createFromEntityScript(EntityScript $script) {
		$extraction = new EntityScriptExtraction($script->getId(), $script->getModule());
		$extraction->setLabel($script->getLabel());
		$extraction->setPluralLabel($script->getPluralLabel());
		$extraction->setTypeChangeMode($script->getTypeChangeMode());
		$extraction->setKnownStringPattern($script->getKnownStringPattern());
		$extraction->setEntityClassName($script->getEntityModel()->getClass()->getName());
		$extraction->setDataSourceName($script->getDataSourceName());
		$extraction->setDraftHistorySize($script->getDraftHistorySize());
			
		$extraction->setDefaultSortDirections($script->getDefaultSortDirections());
			
		if (null !== ($previewControllerClass = $script->getPreviewControllerClass())) {
			$extraction->setPreviewControllerClassName($previewControllerClass->getName());
		}
			
		foreach ($script->getFieldCollection()->filterLevel(true) as $scriptField) {
			$fieldExtraction = new ScriptFieldExtraction();
			$fieldExtraction->setId($scriptField->getId());
			$fieldExtraction->setClassName(get_class($scriptField));
			$fieldExtraction->setLabel($scriptField->getLabel());
			$fieldExtraction->setProps($scriptField->getAttributes()->toArray());

			if ($scriptField instanceof PropertyScriptField) {
				$fieldExtraction->setPropertyName($scriptField->getPropertyAccessProxy()
						->getPropertyName());
			}

			if ($scriptField instanceof EntityPropertyScriptField) {
				$fieldExtraction->setEntityPropertyName($scriptField->getEntityProperty()
						->getName());
			}

			$extraction->putFieldExtraction($fieldExtraction);
		}
			
		foreach ($script->getCommandCollection()->filterLevel(true) as $command) {
			$extraction->putCommandExtraction(self::createScriptElementExtraction($command));
		}
			
		foreach ($script->getModificatorCollection()->filterLevel(true) as $constraint) {
			$extraction->putConstraintExtraction(self::createScriptElementExtraction($constraint));
		}
			
		foreach ($script->getListenerCollection()->filterLevel(true) as $listener) {
			$extraction->putListenerExtraction(self::createScriptElementExtraction($listener));
		}
			
		if (null !== ($defaultMask = $script->getDefaultMask())) {
			$extraction->setDefaultMaskId($defaultMask->getId());
		}
		
		foreach ($script->getMaskSet() as $mask) {
			$extraction->addMaskExtraction($mask->getExtraction());
		}
		
		return $extraction;
	}
	
	private static function createScriptElementExtraction(IndependentScriptElement $configurable) {
		$ce = new ScriptElementExtraction();
		$ce->setId($configurable->getId());
		$ce->setClassName(get_class($configurable));
		$ce->setProps($configurable->getAttributes()->toArray());
		return $ce;
	}
}