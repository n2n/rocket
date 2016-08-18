<?php
namespace rocket\script\config\model;
 
use n2n\core\MessageCode;
use rocket\script\entity\field\EntityPropertyScriptField;
use n2n\dispatch\map\BindingErrors;
use n2n\persistence\DbhPool;
use n2n\dispatch\val\ValNotEmpty;
use n2n\core\TypeNotFoundException;
use n2n\dispatch\map\BindingConstraints;
use n2n\reflection\ReflectionUtils;
use rocket\script\core\ScriptManager;
use n2n\dispatch\DispatchAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use rocket\script\entity\EntityScript;
use n2n\dispatch\Dispatchable;
use n2n\util\Attributes;
use rocket\script\core\extr\EntityScriptExtraction;
use n2n\reflection\property\PropertiesAnalyzer;
use rocket\script\core\ScriptElementStore;
use n2n\persistence\orm\EntityModel;
use rocket\script\entity\field\PropertyScriptField;
use rocket\script\entity\field\AssignableScriptField;
use rocket\script\core\CompatibilityTest;
use n2n\dispatch\val\ValNumeric;
use n2n\dispatch\PropertyPathPart;
use rocket\script\core\extr\ScriptElementExtraction;
use rocket\script\core\extr\ScriptFieldExtraction;
use rocket\script\entity\field\ScriptField;

class EntityScriptForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, 
				array('names' => array('label', 'pluralLabel', 'typeChangeMode', 'draftHistorySize', 'dataSourceName', 'defaultSortFieldIds',
						'defaultSortDirections', 'previewControllerClassName', 'commandClassNames', 'commandIds', 
						'fieldClassNames', 'fieldIds', 'fieldPropertyNames', 'fieldEntityPropertyNames', 'fieldLabels', 
						'constraintClassNames', 'constraintIds', 'listenerClassNames', 'listenerIds')));
		$as->m('save', DispatchAnnotations::MANAGED_METHOD);
		$as->m('saveAndGoToOverview', DispatchAnnotations::MANAGED_METHOD);
		$as->m('saveAndConfig', DispatchAnnotations::MANAGED_METHOD);
	}

	private $extraction;
	private $scriptManager;
	private $scriptElementStore;
	private $dbhPool;
	
	private $availableEntityProperties;
	private $availablePropertiesAccessProxies;
	
	private $knownFielsdDataAttrs = array();
	private $knownPropertiesDataAttrs = array();
	private $inheritedCommandClassNames = array();
	private $inheritedConstraintClassNames = array();
	private $inheritedListenerClassNames = array();
	
	private $sortDirectionOptions = array();
	private $defaultSortFieldIds = array();
	private $defaultSortDirections = array();
	
	private $commandIds = array();
	private $commandClassNames = array();
	
	private $fieldIds = array();
	private $fieldPropertyNames = array();
	private $fieldEntityPropertyNames = array();
	private $fieldClassNames = array();
	private $fieldLabels = array();
	
	private $constraintIds = array();
	private $constraintClassNames = array();
	
	private $listenerIds = array();
	private $listenerClassNames = array();
	
	public function __construct(EntityScriptExtraction $extraction, ScriptManager $scriptManager, 
			ScriptElementStore $scriptElementStore, DbhPool $dbhPool) {
		$this->extraction = $extraction;
		$this->scriptManager = $scriptManager;
		$this->scriptElementStore = $scriptElementStore;
		$this->dbhPool = $dbhPool;
		
		$this->init();
		$this->initScriptElements();
		
// 		$this->sortDirectionOptions = array_combine(OrderDirection::getValues(), OrderDirection::getValues());
		
// 		$this->defaultSortMap = $this->extraction->getDefaultSortMap();
	}
	
	private function init() {
		$entityModel = $this->scriptManager->getEntityModelManager()->getEntityModelByClass(
				ReflectionUtils::createReflectionClass($this->extraction->getEntityClassName()));
		$this->availableEntityProperties = $entityModel->getProperties();
		
		$propertiesAnalyzer = new PropertiesAnalyzer($entityModel->getClass(), false);
		$propertiesAnalyzer->setSuperIgnored(true);
		$this->availablePropertiesAccessProxies = $propertiesAnalyzer->analyzeProperties(false, false);
	
		$this->initScriptElementMeta($entityModel);
	}
	
	private function initScriptElements() {
		foreach ($this->extraction->getCommandExtractions() as $commandExtraction) {
			$this->commandIds[] = $commandExtraction->getId();
			$this->commandClassNames[] = $commandExtraction->getClassName();
		}
		
		foreach ($this->extraction->getFieldExtractions() as $fieldExtraction) {
			$this->fieldIds[$fieldExtraction->getId()] = $fieldExtraction->getId();
			$this->fieldPropertyNames[$fieldExtraction->getId()] = $fieldExtraction->getPropertyName();
			$this->fieldEntityPropertyNames[$fieldExtraction->getId()] = $fieldExtraction->getPropertyName();
			$this->fieldClassNames[$fieldExtraction->getId()] = $fieldExtraction->getClassName();
			$this->fieldLabels[$fieldExtraction->getId()] = $fieldExtraction->getLabel();
		}
		
		foreach ($this->extraction->getConstraintExtractions() as $constraintExtraction) {
			$this->constraintIds[] = $constraintExtraction->getId();
			$this->constraintClassNames[] = $constraintExtraction->getClassName();
		}
		
		foreach ($this->extraction->getListenerExtractions() as $listenerExtraction) {
			$this->listenerIds[] = $listenerExtraction->getId();
			$this->listenerClassNames[] = $listenerExtraction->getClassName();
		}
	}
	
	private function initScriptElementMeta(EntityModel $entityModel) {
		$dummyEntityScript = new EntityScript($this->extraction->getId(), $this->getLabel(), $this->getPluralLabel(), 
				$this->extraction->getModule(),  $entityModel);
		
		$propertiesAnalyzer = new PropertiesAnalyzer($entityModel->getClass(), false);
		$propertiesAnalyzer->setSuperIgnored(true);

		$dummyFields = array();
		foreach ($this->scriptElementStore->getFieldClasses() as $fieldClass) {
			$field =  $fieldClass->newInstance(new Attributes());
			$field->setEntityScript($dummyEntityScript);
			$dummyFields[$fieldClass->getName()] = $field;
		}
		
		$this->knownFielsdDataAttrs = array();
		foreach ($dummyFields as $fieldClassName => $dummyField) {
			$this->knownFielsdDataAttrs[] = $this->createFielDataAttrs($fieldClassName, $dummyField);
		}
				
		$this->knownPropertiesDataAttrs = array();
		foreach ($entityModel->getLevelProperties() as $entityProperty) {
			$propertyName = $entityProperty->getName();
			$propertyAccessProxy = $propertiesAnalyzer->analyzeProperty($propertyName, false, false, false);
			
			$compatibleFieldInfos = array();
			foreach ($dummyFields as $fieldClassName => $dummyField) {
				if (!($dummyField instanceof AssignableScriptField) 
						|| ($propertyAccessProxy === null && $dummyField instanceof PropertyScriptField)) {
					continue;
				}
				
				$compatibilityTest = new CompatibilityTest($entityProperty, $propertyAccessProxy);
				$dummyField->checkCompatibility($compatibilityTest);
				if ($compatibilityTest->hasFailed()) continue;
				
				$compatibleFieldInfos[] = $this->createFielDataAttrs($fieldClassName, $dummyField); 
			}
			
			$this->knownPropertiesDataAttrs[$propertyName] = array(
						'suggestedLabel' => ReflectionUtils::prettyName($propertyName),
						'compatibleFields' => $compatibleFieldInfos);
		}
	}
	
	private function createFielDataAttrs($fieldClassName, ScriptField $dummyField) {
		return array('fieldClassName' => $fieldClassName,
				'entityPropertyRequired' => $dummyField instanceof EntityPropertyScriptField,
				'propertyRequired' => $dummyField instanceof PropertyScriptField,
				'typeName' => $dummyField->getTypeName());
	}
	
	public function getExtraction() {
		return $this->extraction;
	}
	
	public function getLabel() {
		return $this->extraction->getLabel();
	}
	
	public function setLabel($label) {
		$this->extraction->setLabel($label);
	}
	
	public function getPluralLabel() {
		return $this->extraction->getPluralLabel();
	}
	
	public function setPluralLabel($pluralLabel) {
		$this->extraction->setPluralLabel($pluralLabel);
	}
	
	public function getTypeChangeMode() {
		return $this->extraction->getTypeChangeMode();	
	}
	
	public function setTypeChangeMode($typeChangeMode) {
		$this->extraction->setTypeChangeMode($typeChangeMode);
	}
	
	public function getTypeChangeModeOptions() {
		$options = array();
		foreach (EntityScript::getTypeChangeModes() as $mode) {
			$options[$mode] = ReflectionUtils::prettyName($mode);
		}
		return $options;
	}
	
	public function getDraftHistorySize() {
		return $this->extraction->getDraftHistorySize();
	}
	
	public function setDraftHistorySize($draftHistorySize) {
		$this->extraction->setDraftHistorySize($draftHistorySize);
	}
	
	public function getDataSourceName() {
		return $this->extraction->getDataSourceName();
	}
	
	public function setDataSourceName($dataSourceName) {
		$this->extraction->setDataSourceName($dataSourceName);
	}
	
	public function getDataSourceNameOptions() {
		$options = array(DbhPool::DEFAULT_DS_NAME => DbhPool::DEFAULT_DS_NAME);
		foreach ($this->dbhPool->getAvailablePersistenceUnitNames() as $dataSourceName) {
			if (DbhPool::DEFAULT_DS_NAME == $dataSourceName) continue;
			$options[$dataSourceName] = $dataSourceName;
		}
		
		$currentDataSourceName = $this->getDataSourceName();
		if (isset($currentDataSourceName) && !isset($options[$currentDataSourceName])) {
			$options[$currentDataSourceName] = $currentDataSourceName;
		}
		
		return $options;
	}
	
	public function getSortDirectionOptions() {
		return $this->sortDirectionOptions;
	}
	
	public function setPreviewControllerClassName($previewControllerClassName) {
		$this->extraction->setPreviewControllerClassName($previewControllerClassName);
	}
	
	public function getPreviewControllerClassName() {
		return $this->extraction->getPreviewControllerClassName();
	}
	
	public function getDefaultSortFieldIds() {
		return $this->defaultSortFieldIds;
	}

	public function setDefaultSortFieldIds(array $defaultSortFieldIds) {
		$this->defaultSortFieldIds = $defaultSortFieldIds;
	}

	public function getDefaultSortDirections() {
		return $this->defaultSortDirections;
	}

	public function setDefaultSortDirections(array $defaultSortDirections) {
		$this->defaultSortDirections = $defaultSortDirections;
	}

	public function getInheritedCommandClassNames() {
		return $this->inheritedCommandClassNames;
	}

	public function getAvailableCommandClasses() {
		return $this->scriptElementStore->getCommandClasses();
	}
	
	public function getAvailableCommandGroups() {
		return $this->scriptElementStore->getCommandGroups();
	}

	public function getCommandIds() {
		return $this->commandIds;
	}

	public function setCommandIds(array $commandIds) {
		$this->commandIds = $commandIds;
	}

	public function getCommandClassNames() {
		return $this->commandClassNames;
	}

	public function setCommandClassNames(array $commandClassNames) {
		$this->commandClassNames = $commandClassNames;
	}
	
	public function getKnownFieldDataAttrs() {
		return $this->knownFielsdDataAttrs;
	}
	
	public function getKnownPropertiesDataAttrs() {
		return $this->knownPropertiesDataAttrs;
	}
	
	public function getFieldIds() {
		return $this->fieldIds;
	}

	public function setFieldIds(array $fieldIds) {
		$this->fieldIds = $fieldIds;
	}

	public function getFieldPropertyNames() {
		return $this->fieldPropertyNames;
	}

	public function setFieldPropertyNames(array $fieldPropertyNames) {
		$this->fieldPropertyNames = $fieldPropertyNames;
	}

	public function getFieldEntityPropertyNames() {
		return $this->fieldEntityPropertyNames;
	}

	public function setFieldEntityPropertyNames(array $fieldEntityPropertyNames) {
		$this->fieldEntityPropertyNames = $fieldEntityPropertyNames;
	}

	public function getFieldClassNames() {
		return $this->fieldClassNames;
	}

	public function setFieldClassNames(array $fieldClassNames) {
		$this->fieldClassNames = $fieldClassNames;
	}

	public function getFieldLabels() {
		return $this->fieldLabels;
	}

	public function setFieldLabels(array $fieldLabels) {
		$this->fieldLabels = $fieldLabels;
	}
	
	public function getInheritedConstraintClassNames() {
		return $this->inheritedConstraintClassNames;
	}
	
	public function getAvailableConstraintClasses() {
		return $this->scriptElementStore->getConstraintClasses();
	}

	public function getConstraintIds() {
		return $this->constraintIds;
	}

	public function setConstraintIds(array $constraintIds) {
		$this->constraintIds = $constraintIds;
	}

	public function getConstraintClassNames() {
		return $this->constraintClassNames;
	}

	public function setConstraintClassNames(array $constraintClassNames) {
		$this->constraintClassNames = $constraintClassNames;
	}
	
	public function getInheritedListenerClassNames() {
		return $this->inheritedListenerClassNames;
	}
	
	public function getAvailableListenerClasses() {
		return $this->scriptElementStore->getListenerClasses();
	}

	public function getListenerIds() {
		return $this->listenerIds;
	}

	public function setListenerIds(array $listenerIds) {
		$this->listenerIds = $listenerIds;
	}

	public function getListenerClassNames() {
		return $this->listenerClassNames;
	}

	public function setListenerClassNames(array $listenerClassNames) {
		$this->listenerClassNames = $listenerClassNames;
	}
	
	private static function validateType($propertyName, BindingErrors $bindingErrors, $className, $requiredType, $isInterface) {
		$class = null;
		try {
			$class = ReflectionUtils::createReflectionClass($className);
		} catch (TypeNotFoundException $e) {
			$bindingErrors->addError($propertyName, new MessageCode('script_class_not_found_err',
					array('class' => $className)));
			return;
		}
		
		if ($isInterface) {
			if ($class->implementsInterface($requiredType)) return;
			$bindingErrors->addError($propertyName,
					new MessageCode('script_class_requires_interface_err',
							array('class' => $className, 'interface' => $requiredType)));
		} else {
			if ($class->isSubclassOf($requiredType)) return;
			$bindingErrors->addError($propertyName, 
					new MessageCode('script_class_requires_super_class_err',
							array('class' => $className, 'super_class' => $requiredType)));
		}
	}
	
	private function _validation(BindingConstraints $bc) { 
		$bc->val('label', new ValNotEmpty());
		$bc->val('pluralLabel', new ValNotEmpty());
		$bc->val('draftHistorySize', new ValNumeric(false));
		
		$bc->addClosureValidator(function($previewControllerClassName, BindingErrors $bindingErrors) {
			if ($previewControllerClassName === null) return;
			
			self::validateType('previewControllerClassName', $bindingErrors,  
					$previewControllerClassName, 'rocket\script\controller\preview\PreviewController', false);
		});
		
		$bc->addClosureValidator(function(array $commandClassNames, BindingErrors $bindingErrors) {
			foreach ($commandClassNames as $key => $listenerClassName) {
				self::validateType(new PropertyPathPart('commandClassNames', true, $key), $bindingErrors,
						$listenerClassName, 'rocket\script\entity\command\IndependentScriptCommand', true);
			}
		});
		
		$bc->addClosureValidator(function(array $fieldClassNames, array $fieldEntityPropertyNames, 
				array $fieldPropertyNames, array $fieldLabels, BindingErrors $bindingErrors) {
			foreach ($fieldClassNames as $key => $fieldClassName) {
				$fieldClass = self::validateType(new PropertyPathPart('fieldClassName', true, $key), $bindingErrors,
						$fieldClassName, 'rocket\script\entity\field\IndependentScriptField', true);
				if ($fieldClass === null) return;
				
				if (!isset($fieldLabels[$key])) {
					$bindingErrors->addError(new PropertyPathPart('fiedLabels', true, $key),
							new MessageCode('script_field_label_required',
									array('fieldClassName' => $fieldClassName)));
				}
				
				if ($fieldClass->implementsInterface('rocket\script\entity\field\EntityPropertyScriptField')
						&& !isset($fiedEntityPropertyNames[$key])) {
					$bindingErrors->addError(new PropertyPathPart('fiedEntityPropertyNames', true, $key),
							new MessageCode('script_entity_property_required',
									array('fieldClassName' => $fieldClassName)));
				}
				
				if ($fieldClass->implementsInterface('rocket\script\entity\field\PropertyScriptField')
						&& !isset($fieldPropertyNames[$key])) {
					$bindingErrors->addError(new PropertyPathPart('fieldPropertyNames', true, $key), 
							new MessageCode('script_property_required',
									array('fieldClassName' => $fieldClassName)));
				}
			}
		});
		
		$bc->addClosureValidator(function(array $constraintClassNames, BindingErrors $bindingErrors) {
			foreach ($constraintClassNames as $key => $constraintClassName) {
				self::validateType(new PropertyPathPart('constraintClassNames', true, $key), $bindingErrors,
						$constraintClassName, 'rocket\script\entity\modificator\IndependentScriptModificator', true);
			}
		});
		
		$bc->addClosureValidator(function(array $listenerClassNames, BindingErrors $bindingErrors) {
			foreach ($listenerClassNames as $key => $listenerClassName) {
				self::validateType(new PropertyPathPart('listenerClassNames', true, $key), $bindingErrors,
						$listenerClassName, 'rocket\script\entity\listener\IndependentScriptListener', true);
			}
		});
	}
	
	public function save() {
// 		$this->saveDefaultSortConstraints();
		$this->saveCommands();
		$this->saveFields();
		$this->saveConstraints();
		$this->saveListeners();
	
		$this->scriptManager->putScript($this->extraction->createScript($this->scriptManager));
		$this->scriptManager->flush();
	}
	
	public function saveAndGoToOverview() {
		$this->save();
	}
	
	public function saveAndConfig() {
		$this->save();
	}
	
// 	private function saveDefaultSortConstraints() {
// 		$defaultSortConstraints = array();
// 		foreach ($this->defaultSortMap as $sortConstraintModel) {
// 			if (is_null($sortConstraintModel->getScriptFieldId())) continue;
// 			try {
// 				$scriptField = $this->extraction->getFieldCollection()->getById($sortConstraintModel->getScriptFieldId());
// 				if (!($scriptField instanceof EntityPropertyScriptField)) continue;
// 				$defaultSortConstraints[] = new SortCriteriaConstraint($scriptField->getEntityProperty()->getName(), 
// 						$sortConstraintModel->getDirection());
// 			} catch (UnknownScriptElementException $e) { }
// 		}
		
// 		$this->extraction->setDefaultSortConstraints($defaultSortConstraints);
// 	}
	
	private function saveFields() {
		$oldExtractions = $this->extraction->getFieldExtractions();
		$this->extraction->removeFieldExtractions();

		foreach ($this->fieldClassNames as $key => $fieldClassName) {
			$newExtraction = null;
			$id = null;
			if (isset($this->fieldIds[$key])) {
				$id = $this->fieldIds[$key];
			
				foreach ($oldExtractions as $oldExtraction) {
					if ($id == $oldExtraction->getId()) {
						$newExtraction = $oldExtraction;
						break;
					}
				}
			} 

			if ($newExtraction === null) {
				$newExtraction = new ScriptFieldExtraction();
			}	
			
			$newExtraction->setId($id);
			$newExtraction->setClassName($fieldClassName);
			$newExtraction->setLabel($this->fieldLabels[$key]);
			
			if (isset($this->fieldEntityPropertyNames[$key])) {
				$newExtraction->setEntityPropertyName($this->fieldEntityPropertyNames[$key]);
			}
			
			if (isset($this->fieldPropertyNames[$key])) {
				$newExtraction->setPropertyName($this->fieldPropertyNames[$key]);
			}
			
			$this->extraction->putFieldExtraction($newExtraction);
		}
	}
	
	private function saveCommands() {
		$oldExtractions = $this->extraction->getCommandExtractions();
		$this->extraction->removeCommandExtractions();

		foreach ($this->commandClassNames as $key => $commandClassName) {
			$newExtraction = null;
			$id = null;
			if (isset($this->commandIds[$key])) {
				$id = $this->commandIds[$key];
			
				foreach ($oldExtractions as $oldExtraction) {
					if ($id == $oldExtraction->getId()) {
						$newExtraction = $oldExtraction;
						break;
					}
				}
			} 

			if ($newExtraction === null) {
				$newExtraction = new ScriptElementExtraction();
			}	
			
			$newExtraction->setId($id);
			$newExtraction->setClassName($commandClassName);
			
			$this->extraction->putCommandExtraction($newExtraction);
		}
	}
	
	private function saveConstraints() {
		$oldExtractions = $this->extraction->getConstraintExtractions();
		$this->extraction->removeConstraintExtractions();

		foreach ($this->constraintClassNames as $key => $constraintClassName) {
			$newExtraction = null;
			$id = null;
			if (isset($this->constraintIds[$key])) {
				$id = $this->constraintIds[$key];
			
				foreach ($oldExtractions as $oldExtraction) {
					if ($id == $oldExtraction->getId()) {
						$newExtraction = $oldExtraction;
						break;
					}
				}
			} 

			if ($newExtraction === null) {
				$newExtraction = new ScriptElementExtraction();
			}	
			
			$newExtraction->setId($id);
			$newExtraction->setClassName($constraintClassName);
			
			$this->extraction->putConstraintExtraction($newExtraction);
		}
	}
	
	private function saveListeners() {
		$oldExtractions = $this->extraction->getListenerExtractions();
		$this->extraction->removeListenerExtractions();

		foreach ($this->listenerClassNames as $key => $commandClassName) {
			$newExtraction = null;
			$id = null;
			if (isset($this->listenerIds[$key])) {
				$id = $this->listenerIds[$key];
			
				foreach ($oldExtractions as $oldExtraction) {
					if ($id == $oldExtraction->getId()) {
						$newExtraction = $oldExtraction;
						break;
					}
				}
			} 

			if ($newExtraction === null) {
				$newExtraction = new ScriptElementExtraction();
			}	
			
			$newExtraction->setId($id);
			$newExtraction->setClassName($commandClassName);
			
			$this->extraction->putListenerExtraction($newExtraction);
		}
	}
}