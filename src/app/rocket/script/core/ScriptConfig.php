<?php
namespace rocket\script\core;

use rocket\script\core\ScriptManager;
use n2n\persistence\orm\EntityModelManager;
use n2n\core\config\source\ConfigSource;
use n2n\core\Module;
use rocket\script\core\extr\EntityScriptExtraction;
use rocket\script\core\extr\ScriptFieldExtraction;
use rocket\script\core\extr\ScriptElementExtraction;
use rocket\script\core\extr\CustomScriptExtraction;
use rocket\script\core\extr\ScriptExtraction;
use rocket\script\core\extr\ScriptMaskExtraction;
use rocket\script\entity\mask\GroupedFieldOrder;
use rocket\script\entity\filter\data\FilterData;

class ScriptConfig {
	const RAW_DATA_SCRIPTS_KEY = 'scripts';
	const RAW_DATA_SCRIPT_TYPE_KEY = 'type';
	const RAW_DATA_SCRIPT_LABEL_KEY = 'label';
	const RAW_DATA_SCRIPT_ENTITY_PLURAL_LABEL_KEY = 'pluralLabel';
	const RAW_DATA_SCRIPT_ENTITY_TYPE_CHANGE_MODE_KEY = 'typeChangeMode';
	const RAW_DATA_SCRIPT_CUSTOM_CONTROLLER_CLASS_KEY = 'controller';
	const RAW_DATA_SCRIPT_ENTITY_DATA_SOURCE_NAME_KEY = 'dataSourceName';
	const RAW_DATA_SCRIPT_ENTITY_DRAFT_HISTORY_SIZE_KEY = 'historySaved';
	const RAW_DATA_SCRIPT_ENTITY_KOWN_STRING_PATTERN_KEY = 'knownStringPattern';
	const RAW_DATA_SCRIPT_ENTITY_PREVIEW_CONTROLLER_CLASS_NAME_KEY = 'previewControllerClassName';
	const RAW_DATA_SCRIPT_ENTITY_DEFAULT_SORT_KEY = 'defaultSort';
	const RAW_DATA_SCRIPT_ENTITY_CLASS_KEY = 'entity';
	
	const RAW_DATA_SCRIPT_ENTITY_ELEMENT_CLASS_KEY = 'class';
	const RAW_DATA_SCRIPT_ENTITY_ELEMENT_PROPS_KEY = 'props';
	
	const RAW_DATA_SCRIPT_ENTITY_FIELDS_KEY = 'fields';
	const RAW_DATA_SCRIPT_ENTITY_FIELD_PROPERTY_KEY = 'property';
	const RAW_DATA_SCRIPT_ENTITY_FIELD_ENTITY_PROPERTY_KEY = 'entityProperty';
	const RAW_DATA_SCRIPT_ENTITY_FIELD_LABEL_KEY = 'label';
	
	const RAW_DATA_SCRIPT_ENTITY_COMMANDS_KEY = 'commands';
	
	const RAW_DATA_SCRIPT_ENTITY_LISTENERS_KEY = 'listeners';
	
	const RAW_DATA_SCRIPT_ENTITY_CONSTRAINTS_KEY = 'constraints';
	
	const RAW_DATA_SCRIPT_ENTITY_DEFAULT_MASK_ID = 'defaultMaskId';
	const RAW_DATA_SCRIPT_ENTITY_MASKS_KEY = 'masks';
	const RAW_DATA_SCRIPT_ENTITY_MASK_DRAFT_DISABLED_KEY = 'draftDisabled';
	const RAW_DATA_SCRIPT_ENTITY_MASK_TRANSLATION_DISABLED_KEY = 'translationDisabled';
	const RAW_DATA_SCRIPT_ENTITY_MASK_LIST_ORDER_KEY = 'listOrder';
	const RAW_DATA_SCRIPT_ENTITY_MASK_ENTRY_ORDER_KEY = 'entryOrder';
	const RAW_DATA_SCRIPT_ENTITY_MASK_DETAIL_ORDER_KEY = 'detailOrder';
	const RAW_DATA_SCRIPT_ENTITY_MASK_EDIT_ORDER_KEY = 'editOrder';
	const RAW_DATA_SCRIPT_ENTITY_MASK_ADD_ORDER_KEY = 'addOrder';
	const RAW_DATA_SCRIPT_ENTITY_MASK_COMMANDS_KEY = 'commandIds';
	const RAW_DATA_SCRIPT_ENTITY_MASK_GROUP_TITLE_KEY = 'title';
	const RAW_DATA_SCRIPT_ENTITY_MASK_GROUP_TYPE_KEY = 'type';
	const RAW_DATA_SCRIPT_ENTITY_MASK_GROUP_FIELD_ORDER_KEY = 'fieldIds';
	const RAW_DATA_SCRIPT_ENTITY_MASK_PARTIAL_CONTROL_ORDER_KEY = 'partialControlOrder';
	const RAW_DATA_SCRIPT_ENTITY_MASK_OVERALL_CONTROL_ORDER_KEY = 'overallControlOrder';
	const RAW_DATA_SCRIPT_ENTITY_MASK_ENTRY_CONTROL_ORDER_KEY = 'entryControlOrder';
	const RAW_DATA_SCRIPT_ENTITY_MASK_FILTER_DATA_KEY = 'filterData';
	
	const SCRIPT_TYPE_ENTITY = 'entity';
	const SCRIPT_TYPE_CUSTOM = 'custom';
	
	private $source;
	private $module;
	private $entityModelManager;
	private $rawData;
	private $scriptIds;
	private $entityScriptIds;
	
	public function __construct(ConfigSource $source, Module $module, EntityModelManager $entityModelManager) {
		$this->source = $source;
		$this->module = $module;
		$this->entityModelManager = $entityModelManager;
		$this->rawData = $source->readArray();
		
		if (!isset($this->rawData[self::RAW_DATA_SCRIPTS_KEY]) || !is_array($this->rawData[self::RAW_DATA_SCRIPTS_KEY])) {
			$this->clear();
			return;
		}
		
		$this->detectScripts();
	} 
	
	public function getModule() {
		return $this->module;
	}
	
	public function flush() {
		$this->source->writeArray($this->rawData);
	}
	
	public function clear() {
		$this->rawData[self::RAW_DATA_SCRIPTS_KEY] = array();
		
		$this->detectScripts();
	}
	
	private function detectScripts() {
		$this->scriptIds = array();
		$this->entityScriptIds = array();
		
		foreach ($this->rawData[self::RAW_DATA_SCRIPTS_KEY] as $scriptId => $scriptRawData) {
			$this->scriptIds[] = $scriptId;
			if (isset($scriptRawData[self::RAW_DATA_SCRIPT_TYPE_KEY])
					&& $scriptRawData[self::RAW_DATA_SCRIPT_TYPE_KEY] == self::SCRIPT_TYPE_ENTITY
					&& isset($scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_CLASS_KEY])) {
				$this->entityScriptIds[$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_CLASS_KEY]] = $scriptId;
			}
		}
	}
	
	public function containsScriptId($id) {
		return in_array($id, $this->scriptIds);
	}
	
	public function containsEntityScriptClass(\ReflectionClass $class) {
		return isset($this->entityScriptIds[$class->getName()]);
	}
	
	public function getScriptIds() {
		return $this->scriptIds;
	}
	
	public function extractScript($id) {
		if (!$this->containsScriptId($id)) {
			return null;
		}
		
		return $this->createScriptExtractionFromRawData($id, $this->rawData[self::RAW_DATA_SCRIPTS_KEY][$id]);
	}
	
	public function extractEntityScript(\ReflectionClass $class) {
		if (!$this->containsEntityScriptClass($class)) {
			return null;
		}
		$id = $this->entityScriptIds[$class->getName()];
		return $this->createScriptExtractionFromRawData($id, $this->rawData[self::RAW_DATA_SCRIPTS_KEY][$id]);
	}
	
	private function extractRawValue(array $scriptRawData, $fieldName, $required = true, $array = false) {
		if (!isset($scriptRawData[$fieldName])) {
			if (!$required) {
				return null;
			}

			throw new PropertyExtractionException('Missing property: ' . $fieldName);
		}
		
		$value = $scriptRawData[$fieldName];
		if ($array) {
			if (is_array($value)) {
				return $value;
			}
				
			throw new PropertyExtractionException('Property ' . $fieldName . ' must be an array.');
		} else {
			if (is_scalar($value)) {
				return $value;
			}

			throw new PropertyExtractionException('Property ' . $fieldName . ' must be scalar.');
		}
	}
	
	private function extractScriptRawValue($scriptId, array $scriptRawData, $fieldName, $required = true, $array = false) {
		try {
			return $this->extractRawValue($scriptRawData, $fieldName, $required, $array);
		} catch (PropertyExtractionException $e) {
			throw $this->source->createCorruptedConfigSourceException(
					ScriptManager::createInvalidScriptConfigurationException($scriptId, $e));
		}
	}
	
	private function extractScriptFieldRawValue($scriptFieldId, array $scriptRawData, $fieldName, $required = true, $array = false) {
		try {
			return $this->extractRawValue($scriptRawData, $fieldName, $required, $array);
		} catch (PropertyExtractionException $e) {
			throw $this->source->createCorruptedConfigSourceException(
					ScriptManager::createInvalidScriptFieldConfigurationException($scriptFieldId, $e));
		}
	}
	
	private function extractScriptCommandRawValue($scriptCommandId, array $scriptRawData, $fieldName, $required = true, $array = false) {
		try {
			return $this->extractRawValue($scriptRawData, $fieldName, $required, $array);
		} catch (PropertyExtractionException $e) {
			throw $this->source->createCorruptedConfigSourceException(
					ScriptManager::createInvalidScriptCommandConfigurationException($scriptCommandId, $e));
		}
	}
	
	private function extractScriptModificatorRawValue($scriptModificatorId, array $scriptRawData, $fieldName, $required = true, $array = false) {
		try {
			return $this->extractRawValue($scriptRawData, $fieldName, $required, $array);
		} catch (PropertyExtractionException $e) {
			throw $this->source->createCorruptedConfigSourceException(
					ScriptManager::createInvalidScriptModificatorConfigurationException($scriptModificatorId, $e));
		}
	}
	
	private function extractScriptListenerRawValue($entityChangeListenerId, array $scriptRawData, $fieldName, $required = true, $array = false) {
		try {
			return $this->extractRawValue($scriptRawData, $fieldName, $required, $array);
		} catch (PropertyExtractionException $e) {
			throw $this->source->createCorruptedConfigSourceException(
					ScriptManager::createInvalidScriptListenerConfigurationException($entityChangeListenerId, $e));
		}
	}
	
	private function extractScriptMaskRawValue($maskId, array $maskRawData, $fieldName, $required = true, $array = false) {
		try {
			return $this->extractRawValue($maskRawData, $fieldName, $required, $array);
		} catch (PropertyExtractionException $e) {
			throw $this->source->createCorruptedConfigSourceException(
					ScriptManager::createInvalidScriptListenerConfigurationException($maskId, $e));
		}
	}

	private function extractScriptMaskFieldOrder($maskId, array $maskRawData, $fieldName) {
		$extr = $this->extractScriptMaskRawValue($maskId, $maskRawData, $fieldName, false, true);
		if ($extr === null) return null;
		$fieldIds = array();
		foreach ($extr as $key => $fieldId) {
			if (!is_array($fieldId)) {
				$fieldIds[$key] = $fieldId;
				continue;
			}
			
			$groupExtraction = new GroupedFieldOrder();
			$groupExtraction->setType($this->extractScriptMaskRawValue($maskId, $fieldId,
					self::RAW_DATA_SCRIPT_ENTITY_MASK_GROUP_TYPE_KEY, false));
			$groupExtraction->setTitle($this->extractScriptMaskRawValue($maskId, $fieldId,
					self::RAW_DATA_SCRIPT_ENTITY_MASK_GROUP_TITLE_KEY));
			$groupExtraction->setFieldOrder((array) $this->extractScriptMaskFieldOrder($maskId, $fieldId, 
					self::RAW_DATA_SCRIPT_ENTITY_MASK_GROUP_FIELD_ORDER_KEY));
			$fieldIds[$key] = $groupExtraction;
		}
		
		return $fieldIds;
	}

	private function createScriptExtractionFromRawData($id, array $scriptRawData) {
		$type = $this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_TYPE_KEY);
		$label = $this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_LABEL_KEY);
		
		if ($type == self::SCRIPT_TYPE_ENTITY) {
			return $this->createEntityScriptExtractionFromRawData($id, $label, $scriptRawData);
		} else if ($type == self::SCRIPT_TYPE_CUSTOM) {
			return $this->createCustomScriptExtractionFromRawData($id, $label, $scriptRawData);
		}

		throw $this->source->createCorruptedConfigSourceException(
				ScriptManager::createInvalidScriptConfigurationException($id, null, 'Unknown type ' . $type));
	}
	
	private function createCustomScriptExtractionFromRawData($id, $label, array $scriptRawData) {
		$extraction = new CustomScriptExtraction($id, $this->module);
		$extraction->setLabel($label);
		$extraction->setControllerClassName($this->extractScriptRawValue(
				$id, $scriptRawData, self::RAW_DATA_SCRIPT_CUSTOM_CONTROLLER_CLASS_KEY));
		return $extraction;
	}

	private function createEntityScriptExtractionFromRawData($id, $label, array $scriptRawData) {
		$extraction = new EntityScriptExtraction($id, $this->module);
		$extraction->setLabel($label);
		$extraction->setEntityClassName($this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_ENTITY_CLASS_KEY));
		
		$pluralLabel = $this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_ENTITY_PLURAL_LABEL_KEY, false);
		if ($pluralLabel === null) $pluralLabel = $label;
		$extraction->setPluralLabel($pluralLabel);
		
		$typeChangeMode = $this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_ENTITY_TYPE_CHANGE_MODE_KEY, false);
		if (null !== $typeChangeMode) $extraction->setTypeChangeMode($typeChangeMode);
		
		$extraction->setKnownStringPattern(
				$this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_ENTITY_KOWN_STRING_PATTERN_KEY));
		$extraction->setDataSourceName(
				$this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_ENTITY_DATA_SOURCE_NAME_KEY, false));
		$extraction->setDraftHistorySize(
				$this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_ENTITY_DRAFT_HISTORY_SIZE_KEY));
		$extraction->setPreviewControllerClassName(
				$this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_ENTITY_PREVIEW_CONTROLLER_CLASS_NAME_KEY, false));

		foreach ((array) $this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_ENTITY_FIELDS_KEY, false, true) 
				as $fieldId => $fieldRawData) {
			$scriptFieldExtraction = new ScriptFieldExtraction();
			// @todo remove 'if' after ... I don't know...
			if (!is_numeric($fieldId)) {
				$scriptFieldExtraction->setId($fieldId);
			}
			$scriptFieldExtraction->setLabel($this->extractScriptFieldRawValue($fieldId, $fieldRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_FIELD_LABEL_KEY));
			$scriptFieldExtraction->setClassName($this->extractScriptFieldRawValue($fieldId, $fieldRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_CLASS_KEY));
			$scriptFieldExtraction->setProps((array) $this->extractScriptFieldRawValue($fieldId, $fieldRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_PROPS_KEY, false, true));
			$scriptFieldExtraction->setEntityPropertyName($this->extractScriptFieldRawValue($fieldId, $fieldRawData,
					self::RAW_DATA_SCRIPT_ENTITY_FIELD_ENTITY_PROPERTY_KEY, false));
			$scriptFieldExtraction->setPropertyName($this->extractScriptFieldRawValue($fieldId, $fieldRawData,
					self::RAW_DATA_SCRIPT_ENTITY_FIELD_PROPERTY_KEY, false));
			$extraction->putFieldExtraction($scriptFieldExtraction);
		}

		foreach ((array) $this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_ENTITY_COMMANDS_KEY, false, true) 
				as $commandId => $scriptCommandRawData) {
			$configurableExtraction = new ScriptElementExtraction();
			// @todo remove 'if' after ... I don't know...
			if (!is_numeric($commandId)) {
				$configurableExtraction->setId($commandId);
			}
			$configurableExtraction->setClassName($this->extractScriptCommandRawValue($commandId, $scriptCommandRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_CLASS_KEY));
			$configurableExtraction->setProps($this->extractScriptCommandRawValue($commandId, $scriptCommandRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_PROPS_KEY, false, true));
			$extraction->putCommandExtraction($configurableExtraction);
		}

		foreach ((array) $this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_ENTITY_CONSTRAINTS_KEY, false, true) 
				as $constraintId => $constraintRawData) {
			$configurableExtraction = new ScriptElementExtraction();
			$configurableExtraction->setId($constraintId);
			$configurableExtraction->setClassName($this->extractScriptModificatorRawValue($constraintId, $constraintRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_CLASS_KEY));
			$configurableExtraction->setProps($this->extractScriptModificatorRawValue($constraintId, $constraintRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_PROPS_KEY, false, true));
			$extraction->putConstraintExtraction($configurableExtraction);
		}

		foreach ((array) $this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_ENTITY_LISTENERS_KEY, false, true) 
				as $listenerId => $listenerRawData) {
			$configurableExtraction = new ScriptElementExtraction();
			$configurableExtraction->setId($listenerId);
			$configurableExtraction->setClassName($this->extractScriptListenerRawValue($listenerId, $listenerRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_CLASS_KEY));
			$configurableExtraction->setProps((array) $this->extractScriptListenerRawValue($listenerId, $listenerRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_PROPS_KEY, false, true));
			$extraction->putListenerExtraction($configurableExtraction);
		}
		
		$extraction->setDefaultMaskId($this->extractScriptRawValue($id, $scriptRawData, 
				self::RAW_DATA_SCRIPT_ENTITY_DEFAULT_MASK_ID, false));
		
		foreach ((array) $this->extractScriptRawValue($id, $scriptRawData, self::RAW_DATA_SCRIPT_ENTITY_MASKS_KEY, false, true)
				as $maskId => $maskRawData) {
			
			$maskExtraction = new ScriptMaskExtraction();
			$maskExtraction->setId($maskId);
			$maskExtraction->setLabel($this->extractScriptMaskRawValue($maskId, $maskRawData, 
					self::RAW_DATA_SCRIPT_LABEL_KEY, false));
			$maskExtraction->setPluralLabel($this->extractScriptMaskRawValue($maskId, $maskRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_PLURAL_LABEL_KEY, false));
			$maskExtraction->setDraftDisabled($this->extractScriptMaskRawValue($maskId, $maskRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_MASK_DRAFT_DISABLED_KEY, false));
			$maskExtraction->setTranslationDisabled($this->extractScriptMaskRawValue($maskId, $maskRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_MASK_TRANSLATION_DISABLED_KEY, false));
			$maskExtraction->setListFieldOrder($this->extractScriptMaskFieldOrder($maskId, $maskRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_MASK_LIST_ORDER_KEY));
			$maskExtraction->setEntryFieldOrder($this->extractScriptMaskFieldOrder($maskId, $maskRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_MASK_ENTRY_ORDER_KEY));
			$maskExtraction->setDetailFieldOrder($this->extractScriptMaskFieldOrder($maskId, $maskRawData,
					self::RAW_DATA_SCRIPT_ENTITY_MASK_DETAIL_ORDER_KEY));
			$maskExtraction->setEditFieldOrder($this->extractScriptMaskFieldOrder($maskId, $maskRawData,
					self::RAW_DATA_SCRIPT_ENTITY_MASK_EDIT_ORDER_KEY));
			$maskExtraction->setAddFieldOrder($this->extractScriptMaskFieldOrder($maskId, $maskRawData,
					self::RAW_DATA_SCRIPT_ENTITY_MASK_ADD_ORDER_KEY));
			$maskExtraction->setCommandIds($this->extractScriptMaskRawValue($maskId, $maskRawData,
					self::RAW_DATA_SCRIPT_ENTITY_MASK_COMMANDS_KEY, false, true));
			$maskExtraction->setPartialControlOrder((array) $this->extractScriptMaskRawValue($maskId, $maskRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_MASK_PARTIAL_CONTROL_ORDER_KEY, false, true));
			$maskExtraction->setOverallControlOrder((array) $this->extractScriptMaskRawValue($maskId, $maskRawData,
					self::RAW_DATA_SCRIPT_ENTITY_MASK_OVERALL_CONTROL_ORDER_KEY, false, true));
			$maskExtraction->setEntryControlOrder((array) $this->extractScriptMaskRawValue($maskId, $maskRawData,
					self::RAW_DATA_SCRIPT_ENTITY_MASK_ENTRY_CONTROL_ORDER_KEY, false, true));
			
			$filterDataArray = $this->extractScriptMaskRawValue($maskId, $maskRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_MASK_FILTER_DATA_KEY, false, true);
			if ($filterDataArray !== null) { 
				$maskExtraction->setFilterData(FilterData::createFromArray($filterDataArray));
			}
			
			if (null !== ($defaultSortDirections = $this->extractScriptMaskRawValue($maskId, $maskRawData, 
					self::RAW_DATA_SCRIPT_ENTITY_DEFAULT_SORT_KEY, false, true))) {
				$maskExtraction->setDefaultSortDirections($defaultSortDirections);
			}
			
			$extraction->addMaskExtraction($maskExtraction);
		}

		$extraction->setDefaultSortDirections((array) $this->extractScriptRawValue($id, $scriptRawData, 
				self::RAW_DATA_SCRIPT_ENTITY_DEFAULT_SORT_KEY, false, true));
		
		return $extraction;
	}
	
	// PUT
	
	public function putScriptExtraction(ScriptExtraction $scriptExtraction) {
		$id = $scriptExtraction->getId();
		$this->rawData[self::RAW_DATA_SCRIPTS_KEY][$id] = $this->createRawDataFromScriptExtraction($scriptExtraction);
		$this->detectScripts();
	} 
	
	private function createRawDataFromScriptExtraction(ScriptExtraction $scriptExtraction) {
		if ($scriptExtraction instanceof CustomScriptExtraction) {
			return $this->createRawDataFromCustomScriptExtraction($scriptExtraction);
		} else if ($scriptExtraction instanceof EntityScriptExtraction) {
			return $this->createRawDataFromEntityScriptExtraction($scriptExtraction);
		}
	}
	
	private function createRawDataFromCustomScriptExtraction(CustomScriptExtraction $customScriptExtraction) {
		$scriptRawData = array();
		$scriptRawData[self::RAW_DATA_SCRIPT_TYPE_KEY] = self::SCRIPT_TYPE_CUSTOM;
		$scriptRawData[self::RAW_DATA_SCRIPT_LABEL_KEY] = $customScriptExtraction->getLabel();
		$scriptRawData[self::RAW_DATA_SCRIPT_CUSTOM_CONTROLLER_CLASS_KEY] = $customScriptExtraction->getControllerClassName();
		return $scriptRawData;
	}
	
	private function createRawDataFromEntityScriptExtraction(EntityScriptExtraction $extraction) {
		$scriptRawData = array();
		$scriptRawData[self::RAW_DATA_SCRIPT_TYPE_KEY] = self::SCRIPT_TYPE_ENTITY;
		$scriptRawData[self::RAW_DATA_SCRIPT_LABEL_KEY] = $extraction->getLabel();
		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_PLURAL_LABEL_KEY] = $extraction->getPluralLabel();
		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_TYPE_CHANGE_MODE_KEY] = $extraction->getTypeChangeMode();
		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_CLASS_KEY] = $extraction->getEntityClassName();
		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_DATA_SOURCE_NAME_KEY] = $extraction->getDataSourceName();
		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_DRAFT_HISTORY_SIZE_KEY] = $extraction->getDraftHistorySize();
		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_KOWN_STRING_PATTERN_KEY] = $extraction->getKnownStringPattern();
		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_DEFAULT_SORT_KEY] = $extraction->getDefaultSortDirections();
		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_PREVIEW_CONTROLLER_CLASS_NAME_KEY] 
				= $extraction->getPreviewControllerClassName();	
		
		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_FIELDS_KEY] = array();
		foreach ($extraction->getFieldExtractions() as $fieldExtraction) {
			$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_FIELDS_KEY][$fieldExtraction->getId()] = array(
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_CLASS_KEY => $fieldExtraction->getClassName(),
					self::RAW_DATA_SCRIPT_ENTITY_FIELD_LABEL_KEY => $fieldExtraction->getLabel(),
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_PROPS_KEY => $fieldExtraction->getProps(),
					self::RAW_DATA_SCRIPT_ENTITY_FIELD_PROPERTY_KEY => $fieldExtraction->getPropertyName(),
					self::RAW_DATA_SCRIPT_ENTITY_FIELD_ENTITY_PROPERTY_KEY => $fieldExtraction->getEntityPropertyName());
		}

		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_COMMANDS_KEY] = array();
		foreach ($extraction->getCommandExtractions() as $commandExtraction) {
			$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_COMMANDS_KEY][$commandExtraction->getId()] = array(
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_CLASS_KEY => $commandExtraction->getClassName(),
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_PROPS_KEY => $commandExtraction->getProps());
		}
		
		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_CONSTRAINTS_KEY] = array();
		foreach ($extraction->getConstraintExtractions() as $constraintExtraction) {
			$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_CONSTRAINTS_KEY][$constraintExtraction->getId()] = array(
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_CLASS_KEY => $constraintExtraction->getClassName(),
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_PROPS_KEY => $constraintExtraction->getProps());
		}
		
		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_LISTENERS_KEY] = array();
		foreach ($extraction->getListenerExtractions() as $listenerExtraction) {
			$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_LISTENERS_KEY][$listenerExtraction->getId()] = array(
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_CLASS_KEY => $listenerExtraction->getClassName(),
					self::RAW_DATA_SCRIPT_ENTITY_ELEMENT_PROPS_KEY => $listenerExtraction->getProps());
		}

		$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_DEFAULT_MASK_ID] = $extraction->getDefaultMaskId(); 
		
		foreach ($extraction->getMaskExtractions() as $maskExtraction) {	
			$maskRawData = array();
			if (null !== ($label = $maskExtraction->getLabel())) {
				$maskRawData[self::RAW_DATA_SCRIPT_LABEL_KEY] = $label;
			}
			
			if (null !== ($pluralLabel = $maskExtraction->getPluralLabel())) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_PLURAL_LABEL_KEY] = $pluralLabel;
			}
			 
			if (null !== ($knownStringPattern = $maskExtraction->getKnownStringPattern())) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_KOWN_STRING_PATTERN_KEY] = $knownStringPattern;
			}
			
			if ($maskExtraction->isDraftDisabled()) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_MASK_DRAFT_DISABLED_KEY] = true;
			}
			
			if ($maskExtraction->isTranslationDisabled()) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_MASK_TRANSLATION_DISABLED_KEY] = true;
			}
			
			if (null !== ($listFieldOrder = $this->createRawDataFromFieldOrder($maskExtraction->getListFieldOrder()))) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_MASK_LIST_ORDER_KEY] = $listFieldOrder;
			}
			
			if (null !== ($entryFieldOrder = $this->createRawDataFromFieldOrder($maskExtraction->getEntryFieldOrder()))) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_MASK_ENTRY_ORDER_KEY] = $entryFieldOrder;
			}
			
			if (null !== ($entryFieldOrder = $this->createRawDataFromFieldOrder($maskExtraction->getDetailFieldOrder()))) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_MASK_DETAIL_ORDER_KEY] = $entryFieldOrder;
			}
			
			if (null !== ($entryFieldOrder = $this->createRawDataFromFieldOrder($maskExtraction->getEditFieldOrder()))) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_MASK_EDIT_ORDER_KEY] = $entryFieldOrder;
			}
			
			if (null !== ($entryFieldOrder = $this->createRawDataFromFieldOrder($maskExtraction->getAddFieldOrder()))) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_MASK_ADD_ORDER_KEY] = $entryFieldOrder;
			}
			
			if (null !== ($commandIds = $this->createRawDataFromFieldOrder($maskExtraction->getCommandIds()))) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_MASK_COMMANDS_KEY] = $commandIds;
			}
			
			if (sizeof($partialControlOrder = $maskExtraction->getPartialControlOrder())) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_MASK_PARTIAL_CONTROL_ORDER_KEY] = array_values($partialControlOrder);
			}
			
			if (sizeof($overallControlOrder = $maskExtraction->getOverallControlOrder())) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_MASK_OVERALL_CONTROL_ORDER_KEY] = array_values($overallControlOrder);
			}
			
			if (sizeof($entryControlOrder = $maskExtraction->getEntryControlOrder())) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_MASK_ENTRY_CONTROL_ORDER_KEY] = array_values($entryControlOrder);
			}
			
			if (null !== ($filterData = $maskExtraction->getFilterData())) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_MASK_FILTER_DATA_KEY] = $filterData->toArray();
			}
			
			if (null !== ($defaultSortDirections = $maskExtraction->getDefaultSortDirections())) {
				$maskRawData[self::RAW_DATA_SCRIPT_ENTITY_DEFAULT_SORT_KEY] = $defaultSortDirections;
			}
			 
			$scriptRawData[self::RAW_DATA_SCRIPT_ENTITY_MASKS_KEY][$maskExtraction->getId()] = $maskRawData;
		}
	
		return $scriptRawData;
	}
	
	
	private function createRawDataFromFieldOrder(array $fieldOrder = null) {
		if ($fieldOrder === null) return null;
		
		$rawData = array();
		foreach ($fieldOrder as $field) {
			if ($field instanceof GroupedFieldOrder) {
				$rawData[] = array(
						self::RAW_DATA_SCRIPT_ENTITY_MASK_GROUP_TITLE_KEY => $field->getTitle(),
						self::RAW_DATA_SCRIPT_ENTITY_MASK_GROUP_TYPE_KEY => $field->getType(),
						self::RAW_DATA_SCRIPT_ENTITY_MASK_GROUP_FIELD_ORDER_KEY => 
									$this->createRawDataFromFieldOrder($field->getFieldOrder()));
			} else {
				$rawData[] = $field;
			}
		}
		
		return $rawData;
	}
	
	// REMOVE
	
	public function removeScriptById($id) {
		unset($this->rawData[self::RAW_DATA_SCRIPTS_KEY][$id]);
		$this->detectScripts();
	}
	
	// UTILS
	
	public function toArray() {
		return $this->rawData;
	}
}

class PropertyExtractionException extends ScriptException {
	
}