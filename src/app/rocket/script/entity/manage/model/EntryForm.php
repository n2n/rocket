<?php
namespace rocket\script\entity\manage\model;

use n2n\dispatch\option\impl\OptionForm;
use n2n\dispatch\DispatchAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\Dispatchable;
use n2n\dispatch\PropertyPath;
use n2n\dispatch\PropertyPathPart;
use n2n\dispatch\map\BindingConstraints;
use n2n\core\IllegalStateException;
use rocket\script\entity\manage\display\DisplayDefinition;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\command\impl\common\model\ListEntryModel;
use rocket\script\entity\manage\mapping\MappingOperationFailedException;

class EntryForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, array('names' => array('selectedTypeId')));
		$as->p('mainEntryFormPart', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
		$as->p('levelEntryFormParts', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY);
	}
		
	private $typeOptions = array();
	private $scriptSeletionMappings = array();
	
	private $selectedTypeId;
	private $mainEntryFormPart;
	private $levelEntryFormParts = array();
		
	public function __construct() {
		
	}
	
	public function addTypeOption(ScriptSelectionMapping $scriptSelectionMapping) {
		$entityScript = $scriptSelectionMapping->determineEntityScript();
		$entityScriptId = $entityScript->getId();
		$this->typeOptions[$entityScriptId] = $entityScript->getLabel();
		$this->scriptSeletionMappings[$entityScriptId] = $scriptSelectionMapping;
		
		while (null !== ($entityScript = $entityScript->getSuperEntityScript())) {
			if (isset($this->scriptSeletionMappings[$entityScript->getId()])) break;
			$this->scriptSeletionMappings[$entityScript->getId()] = $scriptSelectionMapping;
		} 
		
		if ($this->selectedTypeId === $entityScriptId
				|| !isset($this->scriptSeletionMappings[$this->selectedTypeId])) return;
		
		$this->scriptSeletionMappings[$this->selectedTypeId]->copy($scriptSelectionMapping);
	}
	
	public function hasTypeOption($entityScriptId) {
		return isset($this->typeOptions[$entityScriptId]);
	}
	
	public function getTypeOptions() {
		return $this->typeOptions;
	}
	
	public function getScriptSelectionMappingByEntityScriptId($entityScriptId) {
		if (isset($this->scriptSeletionMappings[$entityScriptId])) {
			return $this->scriptSeletionMappings[$entityScriptId];
		}
		return null;
	}
	
	public function getSelectedTypeId() {
		return $this->selectedTypeId;
	}
	
	public function setSelectedTypeId($selectedTypeId) {
		$this->selectedTypeId = $selectedTypeId;
	}
	
	public function setMainEntryFormPart(EntryFormPart $mainEntryFormPart) {
		$this->mainEntryFormPart = $mainEntryFormPart;	
	}
	/**
	 * @return EntryFormPart
	 */
	public function getMainEntryFormPart() {
		return $this->mainEntryFormPart;
	}
	
	public function addLevelEntryFormPart(EntryFormPart $levelEntryFormPart) {
		$this->levelEntryFormParts[$levelEntryFormPart->getDisplayDefinition()->getEntityScript()->getId()] = $levelEntryFormPart; 
	}
	
	public function setLevelEntryFormParts(array $levelEntryFormParts) {
		$this->levelEntryFormParts = $levelEntryFormParts;
	}
	
	public function getLevelEntryFormParts() {
		return $this->levelEntryFormParts;
	}
	
	public function getLevelEntryFormPartByEntityScriptId($entityScriptId) {
		if (isset($this->levelEntryFormParts[$entityScriptId])) {
			return $this->levelEntryFormParts[$entityScriptId];
		}
		return null;
	}
	
	public function getRepresentativeEntryFormPart() {
		if (isset($this->levelEntryFormParts[$this->selectedTypeId])) {
			return $this->levelEntryFormParts[$this->selectedTypeId];
		}
		
		return $this->mainEntryFormPart;
	} 
	
	private function _validation(BindingConstraints $bc) {
		$entityScriptId = $bc->getRawValue('selectedTypeId');
		$toIgnore = array_keys($this->levelEntryFormParts);
		$toIgnore = array_combine($toIgnore, $toIgnore);

		do {
			if (!isset($this->levelEntryFormParts[$entityScriptId])) {
				break;
			}
			
			unset($toIgnore[$entityScriptId]);
			$entityScript = $this->levelEntryFormParts[$entityScriptId]->getDisplayDefinition()->getEntityScript();
			if (null !== ($entityScript = $entityScript->getSuperEntityScript())) {
				$entityScriptId = $entityScript->getId();
			}
		} while ($entityScript !== null);
	
		foreach ($toIgnore as $entityScriptId) {
			$bc->ignore('levelEntryFormParts', $entityScriptId);
		}
	}
	/**
	 * @return ScriptSelectionMapping
	 */
	public function buildScriptSelectionMapping() {
		$scriptSelectionMapping = null;
		if (0 == sizeof($this->levelEntryFormParts)) {
			$scriptSelectionMapping = $this->mainEntryFormPart->getScriptSelectionMapping();
		} else if (isset($this->typeOptions[$this->selectedTypeId])) {
			$scriptSelectionMapping = $this->scriptSeletionMappings[$this->selectedTypeId];
		} else {
			throw IllegalStateException::createDefault();
		}
		
		$entityScript = $scriptSelectionMapping->determineEntityScript();
				
		foreach ($entityScript->getAllSuperEntityScripts(true) as $sEntityScriptId => $sEntityScript) {
// 			if (isset($this->typeSeletionMappings[$sEntityScriptId])
// 					&& !$this->typeSeletionMappings[$sEntityScriptId]->equals($scriptSelectionMapping)) { 
// 				$this->typeSeletionMappings[$sEntityScriptId]->copy($scriptSelectionMapping);
// 			}
						
			if (isset($this->levelEntryFormParts[$sEntityScriptId])) {
				$this->levelEntryFormParts[$sEntityScriptId]->save($scriptSelectionMapping);
			}
		}
		
		$this->mainEntryFormPart->save($scriptSelectionMapping);
		
		return $scriptSelectionMapping;
	}
}

// class EntryFormResult {
// 	private $validationResult;
// 	private $scriptSelectionMapping;
	
// 	public function __construct(ValidationResult $validationResult, ScriptSelectionMapping $scriptSelectionMapping = null) {
// 		$this->validationResult = $validationResult;
// 		$this->scriptSelectionMapping = $scriptSelectionMapping;
// 	}
	
// 	public function isValid() {
// 		return $this->validationResult->isValid();
// 	}
	
// 	public function getScriptSelectionMapping() {
// 		return $this->scriptSelectionMapping;
// 	}
// }

class EntryFormPart implements EditEntryModel, Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->p('optionForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
	}
	
	private $displayDefinition;
	private $scriptState;
	private $scriptSelectionMapping;
	private $optionForm;
	private $listEntryModel;
	
	public function __construct(DisplayDefinition $displayDefinition, ScriptState $scriptState, 
			ScriptSelectionMapping $scriptSelectionMapping) {
		$this->displayDefinition = $displayDefinition;
		$this->scriptState = $scriptState;
		$this->scriptSelectionMapping = $scriptSelectionMapping;
		$this->optionForm = $displayDefinition->createOptionForm($this);
	}
	
	public function getScriptSelectionMapping() {
		return $this->scriptSelectionMapping;
	}
	
	public function getDisplayDefinition() {
		return $this->displayDefinition;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\model\EntryModel::createPropertyPath()
	 */
	public function createPropertyPath($propertyName, PropertyPath $basePropertyPath = null) {
		if ($basePropertyPath === null) {
			return new PropertyPath(array(new PropertyPathPart($propertyName)));
		}	
		
		return $basePropertyPath->ext($propertyName);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\model\EntryModel::getScriptState()
	 */
	public function getScriptState() {
		return $this->scriptState;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\model\EntryModel::getScriptSelection()
	 */
	public function getScriptSelection() {
		return $this->scriptSelectionMapping->getScriptSelection();		
	}
	
	public function hasListEntryModel() {
		return $this->listEntryModel !== null;
	}
	
	public function getListEntryModel() {
		return $this->listEntryModel;
	}
	
	public function setListEntryModel(ListEntryModel $listEntryModel)  {
		$this->listEntryModel = $listEntryModel;
	}

	public function getOptionForm() {
		return $this->optionForm;
	}
	
	public function setOptionForm(OptionForm $optionForm) {
		$this->optionForm = $optionForm;
	} 
	
	private function _validation() {}
	
	public function save(ScriptSelectionMapping $scriptSelectionMapping) {
		$this->displayDefinition->writeAttributes($this->optionForm->getAttributes(),
				$scriptSelectionMapping, $this);
	}
}

// class EntryForm implements EditEntryModel, Dispatchable {
// 	private static function _annotations(AnnotationSet $as) {
// 		$as->p('optionForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
// 		$as->p('subOptionForms', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY);
// 	}
	
// 	private $scriptState;
// 	private $scriptSelection;
// 	private $readOnly;
	
// 	private $entityScript;
// 	private $subEntityScripts;
	
// 	private $visibleScriptFields = array();
// 	private $subVisibleScriptFields = array();
	
// 	protected $selectedTypeId;
// 	protected $optionForm;
// 	protected $subOptionForms = array();
// 	/**
// 	 * @param ScriptState $scriptState 
// 	 * @param EntityScript $entityScript The Script of the Entity which ....
// 	 * @param ScriptSelection $scriptSelection
// 	 * @param string $readOnly
// 	 */
// 	public function __construct(ScriptState $scriptState, EntityScript $entityScript, ScriptSelection $scriptSelection = null, $readOnly = false) {
// 		$this->scriptState = $scriptState;
// 		$this->entityScript = $entityScript;
// 		$this->scriptSelection = $scriptSelection;
// 		$this->readOnly = $readOnly;
// 		$this->selectedTypeId = $entityScript->getId();
// 		$this->entityScripts[$this->selectedTypeId] = $entityScript;
				
// 		$this->optionForm = new OptionForm($this->createOptionCollection($entityScript, false), new Attributes());
		
// 		if ($this->isNew()) {
// 			$subEntityScripts = $entityScript->getAllSubEntityScripts();
			
// 			if (sizeof($subEntityScripts) || $this->entityScript->getEntityModel()->isAbstract()) {
// 				$this->subEntityScripts = array();
			
// 				foreach ($subEntityScripts as $id => $subEntityScript) {
// 					if ($subEntityScript->getEntityModel()->isAbstract()) continue;
// 					$this->subEntityScripts[$id] = $subEntityScript;
// 					$this->subOptionForms[$id] = new OptionForm($this->createOptionCollection($subEntityScript, true), new Attributes());
// 				}
// 			}
// 		} else {
// 			$object = $scriptSelection->getEntity();
// 			$entityModel = EntityModelManager::getInstance()->getEntityModelByObject($object);
				
// 			$subEntityScripts = $entityScript->getAllSubEntityScripts();

// 			if (!$scriptSelection->hasDraft() && !$scriptSelection->hasTranslation() 
// 					&& (sizeof($subEntityScripts) || $this->entityScript->getEntityModel()->isAbstract())) {
// 				$this->subEntityScripts = array();
// 			}
			
// 			foreach ($subEntityScripts as $id => $subEntityScript) {
// 				$subEntityModel = $subEntityScript->getEntityModel();
// 				if ($subEntityModel->isAbstract()) continue;
			
// 				if ($entityModel->equals($subEntityModel)) {
// 					$this->selectedTypeId = $subEntityScript->getId();
// 				}
				
// 				if (isset($this->subEntityScripts)) { 
// 					$this->subEntityScripts[$id] = $subEntityScript;
// 					$this->subOptionForms[$id] = new OptionForm($this->createOptionCollection($subEntityScript, true), new Attributes());
// 				}
// 			}
				
// 			$this->readFromObject($scriptSelection->getEntity());
// 		}
// 	}
	
// 	private function createOptionCollection(EntityScript $entityScript, $levelOnly) {
// 		$entityScriptId = $entityScript->getId();
// 		if ($levelOnly && !isset($this->subVisibleScriptFields[$entityScriptId])) {
// 			$this->subVisibleScriptFields[$entityScriptId] = array();
// 		}
		
// 		$optionCollection = new OptionCollectionImpl();
// 		foreach ($entityScript->getFieldCollection()->toArray() as $scriptFieldId => $scriptField) {
// 			if (!($scriptField instanceof Displayable) || !$scriptField->isDisplayInEditViewEnabled()) continue;

// 			if (!$levelOnly) {
// 				$this->visibleScriptFields[$scriptFieldId] = $scriptField;
// 			}
			
// 			if (!($scriptField instanceof Editable) || $scriptField->isReadOnly() || $this->readOnly
// 					|| (isset($this->scriptSelection) && !$this->scriptSelection->isWritingAllowed($scriptField))) continue;
			
// 			if ($levelOnly) {
// 				if ($this->optionForm->containsPropertyName($scriptField->getPropertyName())) continue;
// 				$this->subVisibleScriptFields[$entityScriptId][$scriptFieldId] = $scriptField;
// 			}
			
// 			if (isset($this->scriptSelection)) {
// 				if ($this->scriptSelection->hasDraft() && !($scriptField instanceof DraftableScriptField)) {
// 					continue;
// 				}
			
// 				if ($this->scriptSelection->hasTranslation() && !($scriptField instanceof TranslatableScriptField
// 						&& $scriptField->isTranslationEnabled())) {
// 					continue;
// 				}
// 			}
				
// 			$optionCollection->addOption($scriptField->getPropertyName(), 
// 					$scriptField->createOption($this->scriptState, $this->scriptSelection));
// 		}
		
// 		return $optionCollection;
// 	}
	
// 	private function readFromObject(Entity $object) {
// 		$selectedEntityScript = $this->getSelectedEntityScript();
// 		$selectedEntityScriptId = $selectedEntityScript->getId();
// 		if (isset($this->subOptionForms[$selectedEntityScriptId])) {
// 			$this->readProperties($selectedEntityScript, $this->subOptionForms[$selectedEntityScriptId], $object);
// 		}
		
// 		$this->readProperties($this->entityScript, $this->optionForm, $object);
// 	}
	
// 	private function readProperties(EntityScript $entityScript, OptionCollectionDispatchable $optionForm, Entity $object) {
// 		foreach ($entityScript->getFieldCollection()->toArray() as $scriptField) {
// 			if (!($scriptField instanceof Editable)
// 				|| !$optionForm->containsPropertyName($scriptField->getPropertyName())) continue;
				
// 			$propertyName = $scriptField->getPropertyName();
// 			$accessProxy = $scriptField->getPropertyAccessProxy();
			
// 			$optionForm->setAttributeValue($propertyName,
// 					$scriptField->propertyValueToOptionAttributeValue(
// 							$accessProxy->getValue($object), $this->scriptState, $this->scriptSelection));
// 		}
// 	}
	
// 	public function writeToObject(Entity $object) {
// 		$selectedEntityScript = $this->getSelectedEntityScript();
// 		$selectedEntityScriptId = $selectedEntityScript->getId();
// 		if (isset($this->subOptionForms[$selectedEntityScriptId])) {
// 			$this->writeProperties($selectedEntityScript, $this->subOptionForms[$selectedEntityScriptId], $object);
// 		}
		
// 		$this->writeProperties($this->entityScript, $this->optionForm, $object);
// 	}
	
// 	public function writeProperties(EntityScript $entityScript, OptionCollectionDispatchable $optionForm, Entity $object) {
// 		foreach ($entityScript->getFieldCollection()->toArray() as $scriptField) {
// 			if (!($scriptField instanceof Editable)) continue;
						
// 			$propertyName = $scriptField->getPropertyName();
// 			if (!$optionForm->containsPropertyName($propertyName)) continue;
				
// 			$accessProxy = $scriptField->getPropertyAccessProxy();
// 			$accessProxy->setValue($object, $scriptField->optionAttributeValueToPropertyValue(
// 					$optionForm->getAttributeValue($propertyName), $optionForm->getAttributes(),
// 					$object, $this->scriptState, $this->scriptSelection));
// 		}
// 	}
	
// 	public function getScriptState() {
// 		return $this->scriptState;
// 	}
	
// 	public function getEntityScript() {
// 		return $this->entityScript;
// 	}
	
// 	public function getScriptSelection() {
// 		return $this->scriptSelection;
// 	}
	
// 	public function createPropertyPath($propertyName, PropertyPath $basePropertyPath = null) {
// 		if (isset($basePropertyPath)) {
// 			return $basePropertyPath->createExtendedPath(array('optionForm', $propertyName));
// 		}
		
// 		return PropertyPath::createFromPropertyExpressionArray(array('optionForm', $propertyName));
// 	}
	
// 	public function isNew() {
// 		return !isset($this->scriptSelection);
// 	}
	
// 	public function getVisibleScriptFields() {
// 		return $this->visibleScriptFields;
// 	}
	
// 	public function containsPropertyName($propertyName) {
// 		return $this->optionForm->containsPropertyName($propertyName);
// 	}
	
// 	public function getPropertyValueByName($name) {
// 		return $this->optionForm->getPropertyValue($name);
// 	}
// 	/**
// 	 * @return EntityScript
// 	 */
// 	public function getSelectedEntityScript() {
// 		if (isset($this->subEntityScripts[$this->selectedTypeId])) {
// 			return $this->subEntityScripts[$this->selectedTypeId];
// 		}
		
// 		return $this->entityScript;
// 	}
	
// 	public function isTypeSelectionAvailable() {
// 		return isset($this->subEntityScripts);
// 	}
	
// 	public function getSelectedTypeOptions() {
// 		$options = array();
		
// 		if (!$this->entityScript->getEntityModel()->isAbstract()) {
// 			$options[$this->entityScript->getId()] = $this->entityScript->getLabel();
// 		}
		
// 		foreach ($this->subEntityScripts as $id => $subEntityScript) {
// 			$options[$id] = $subEntityScript->getLabel();
// 		}

// 		return $options;
// 	}
	
// 	public function getSubEntityScriptIds() {
// 		return array_keys($this->subVisibleScriptFields);
// 	}
	
// 	public function getSubVisibleScriptFields($scriptId) {
// 		if (isset($this->subVisibleScriptFields[$scriptId])) {
// 			return $this->subVisibleScriptFields[$scriptId]; 
// 		}
		
// 		throw IllegalStateException::createDefault(); 
// 	}
	
// 	public function containsSubPropertyName($scriptId, $propertyName) {
// 		if (isset($this->subOptionForms[$scriptId])) {
// 			return $this->subOptionForms[$scriptId]->containsPropertyName($propertyName);
// 		}
		
// 		throw IllegalStateException::createDefault();
// 	}
	
// 	public function createSubPropertyPath($scriptId, $propertyName, PropertyPath $basePropertyPath = null) {
// 		if (isset($basePropertyPath)) {
// 			return $basePropertyPath->createExtendedPath(
// 					array(new PropertyPathPart('subOptionForms', true, $scriptId), $propertyName));
// 		}
		
// 		return PropertyPath::createFromPropertyExpressionArray(
// 				array(new PropertyPathPart('subOptionForms', true, $scriptId), $propertyName));
// 	}
	
// 	public function getSelectedTypeId() {
// 		return $this->selectedTypeId;
// 	}
	
// 	public function setSelectedTypeId($selectedTypeId) {
// 		$this->selectedTypeId = $selectedTypeId;
// 	}
	
// 	public function getOptionForm() {
// 		return $this->optionForm;
// 	}
	
// 	public function setOptionForm(OptionForm $optionForm) {
// 		$this->optionForm = $optionForm;
// 	}
	
// 	public function getSubOptionForms() {
// 		return $this->subOptionForms;
// 	}
	
// 	public function setSubOptionForms(array $subOptionForms) {
// 		$this->subOptionForms = $subOptionForms;
// 	}
	
// 	private function _validation(BindingConstraints $bc) {
// 		if (!$this->isTypeSelectionAvailable()) return;
// 		$bc->val('selectedTypeId', new ValEnum(array_keys($this->getSelectedTypeOptions())));
		
// 		$selectedTypeId = $bc->getRawValue('selectedTypeId');
// 		foreach ($this->subOptionForms as $key => $subOptionCollectionDispatchable) {
// 			if ($key == $selectedTypeId) continue;
		
// 			$bc->ignore('subOptionForms', $key);
// 		}
// 	}
// }