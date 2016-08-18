<?php

namespace rocket\script\core\extr;

use n2n\core\TypeNotFoundException;
use n2n\reflection\ReflectionUtils;
use rocket\script\entity\EntityScript;
use rocket\script\core\ScriptManager;
use n2n\util\Attributes;
use n2n\persistence\orm\UnknownEntityPropertyException;
use rocket\script\entity\field\EntityPropertyScriptField;
use n2n\reflection\ReflectionRuntimeException;
use rocket\script\entity\field\PropertyScriptField;
use n2n\persistence\orm\EntityModelManager;
use n2n\reflection\property\PropertiesAnalyzer;
use rocket\script\core\CompatibilityTest;
use rocket\script\entity\mask\IndependentScriptMask;
use rocket\script\entity\field\AssignableScriptField;

class EntityScriptFactory {
	private $entityModelManager;
	
	public function __construct(EntityModelManager $entityModelManager) {
		$this->entityModelManager = $entityModelManager;
	}
	
	private function createEntityModel($scriptId, $entityClassName) {
		try {
			return $this->entityModelManager->getEntityModelByClass(
					ReflectionUtils::createReflectionClass($entityClassName));
		} catch (TypeNotFoundException $e) {
			throw ScriptManager::createInvalidScriptConfigurationException($scriptId, $e);
		} catch (\InvalidArgumentException $e) {
			throw ScriptManager::createInvalidScriptConfigurationException($scriptId, $e);
		}
	}
	
	private function createPreviewControllerClass($scriptId, $previewControllerClassName) {
		try {
			$previewControllerClass = ReflectionUtils::createReflectionClass($previewControllerClassName);
			if ($previewControllerClass->isSubclassOf('rocket\script\controller\preview\PreviewController')) {
				return $previewControllerClass;
			} 
			throw ScriptManager::createInvalidScriptConfigurationException($scriptId, null,
					$previewControllerClass->getName() . ' must extend rocket\script\controller\preview\PreviewController');
		} catch (TypeNotFoundException $e) {
			throw ScriptManager::createInvalidScriptConfigurationException($scriptId, $e);
		}
	}
	
// 	private function applySortModificatorCollection(EntityScript $entityScript, array $sortMap) {
// 		$sortCollection = $entityScript->getDefaultSortModificatorCollection();
// 		foreach ($sortMap as $scriptFieldId => $direction) {
// 			$previousE = null;
// 			try {
// 				ArgumentUtils::validateEnum($direction, Criteria::getOrderDirections());
		
// 				$scriptField = $entityScript->getFieldCollection()->getById($scriptFieldId);
// 				if ($scriptField instanceof SortableScriptField) {
// 					$sortCollection->addSortableScriptField($scriptField, $direction);
// 				}
// 			} catch (UnknownScriptElementException $e) {
// 				continue;
// 			} catch (\InvalidArgumentException $e) {
// 				throw ScriptManager::createInvalidScriptConfigurationException($entityScript->getId(), $e);
// 			}
// 		}
// 		return $sortCollection;
		
// 	}
	/**
	 * @param EntityScriptExtraction $extraction
	 * @return \rocket\script\entity\EntityScript
	 */
	public function create(EntityScriptExtraction $extraction) {
		$entityScript = new EntityScript($extraction->getId(), $extraction->getLabel(), 
				$extraction->getPluralLabel(), $extraction->getModule(), 
				$this->createEntityModel($extraction->getId(), $extraction->getEntityClassName()));
		$entityScript->setTypeChangeMode($extraction->getTypeChangeMode());
		$entityScript->setKnownStringPattern($extraction->getKnownStringPattern());
		$entityScript->setDataSourceName($extraction->getDataSourceName());
		$entityScript->setDraftHistorySize($extraction->getDraftHistorySize());
		
		if (null !== ($previewControllerClassName = $extraction->getPreviewControllerClassName())) {
			$entityScript->setPreviewControllerClass($this->createPreviewControllerClass(
					$extraction->getId(), $previewControllerClassName));
		}
		
		foreach ($extraction->getFieldExtractions() as $scriptFieldExtraction) {
			$entityScript->getFieldCollection()->add(
					$this->createScriptField($entityScript, $scriptFieldExtraction));
		}
		
		foreach ($extraction->getCommandExtractions() as $configurableExtraction) {
			$entityScript->getCommandCollection()->add(
					$this->createScriptCommand($entityScript, $configurableExtraction));
		}
		
		foreach ($extraction->getConstraintExtractions() as $configurableExtraction) {
			$entityScript->getModificatorCollection()->add(
					$this->createScriptModificator($entityScript, $configurableExtraction));
		}
		
		foreach ($extraction->getListenerExtractions() as $configurableExtraction) {
			$entityScript->getListenerCollection()->add(
					$this->createScriptListener($entityScript, $configurableExtraction));
		}
		
		$entityScript->setDefaultSortDirections($extraction->getDefaultSortDirections());
		
		foreach ($extraction->getMaskExtractions() as $maskExtraction) {
			$entityScript->getMaskSet()->add(new IndependentScriptMask($entityScript, $maskExtraction));
		}
		
		if (null !== ($defaultMaskId = $extraction->getDefaultMaskId())) {
			$entityScript->setDefaultMask($entityScript->getMaskById($defaultMaskId));
		}
		
// 		$entityScript->setPartialControlOrder($extraction->getPartialControlOrder());
// 		$entityScript->setOverallControlOrder($extraction->getOverallControlOrder());
// 		$entityScript->setEntryControlOrder($extraction->getEntryControlOrder());
			
		return $entityScript;
	}
	
	public function createScriptField(EntityScript $entityScript, ScriptFieldExtraction $scriptFieldExtraction) {
		$id = $scriptFieldExtraction->getId();
		$scriptFieldClass = null;
		try {
			$scriptFieldClass = ReflectionUtils::createReflectionClass(
					$scriptFieldExtraction->getClassName());
		} catch (TypeNotFoundException $e) {
			throw ScriptManager::createInvalidScriptFieldConfigurationException($id, $e);
		}
		
		if (!$scriptFieldClass->implementsInterface('rocket\script\entity\field\IndependentScriptField')) {
			throw ScriptManager::createInvalidScriptFieldConfigurationException($id, null,
					'\'' . $scriptFieldClass->getName() . '\' must implement \'rocket\script\entity\field\IndependentScriptField\'.');
		}
		
		$scriptField = $scriptFieldClass->newInstance(new Attributes($scriptFieldExtraction->getProps()));
		$scriptField->setId($id);
		$scriptField->setEntityScript($entityScript);
		$scriptField->setLabel($scriptFieldExtraction->getLabel());
		
		if (!($scriptField instanceof AssignableScriptField)) {
			return $scriptField;
		}
		
		$propertyAccessProxy = null;
		if ($scriptField instanceof PropertyScriptField) {				
			try {
				$propertyName = $scriptFieldExtraction->getPropertyName();
				if ($propertyName === null) {
					throw ScriptManager::createInvalidScriptFieldConfigurationException($id, null, 'No property name defined.');
				}
				$propertiesAnalyzer = new PropertiesAnalyzer($entityScript->getEntityModel()->getClass(), false);
				$propertyAccessProxy = $propertiesAnalyzer->analyzeProperty($propertyName, false, true);
				$propertyAccessProxy->setNullReturnAllowed(true);
				$scriptField->setPropertyAccessProxy($propertyAccessProxy);
			} catch (ReflectionRuntimeException $e) {
				throw ScriptManager::createInvalidScriptFieldConfigurationException($id, $e,
						'ScriptField is not compatible with its assigned property');
			}
		}
		
		$entityProperty = null;
		if ($scriptField instanceof EntityPropertyScriptField) {
			$entityPropertyName = $scriptFieldExtraction->getEntityPropertyName();
			if ($entityPropertyName === null) {
				throw ScriptManager::createInvalidScriptFieldConfigurationException($id, null, 'No entity property name defined.');
			}
			
			try {
				$entityProperty = $entityScript->getEntityModel()->getPropertyByName($entityPropertyName, true);
				$scriptField->setEntityProperty($entityProperty);
			} catch (UnknownEntityPropertyException $e) {
				throw ScriptManager::createInvalidScriptFieldConfigurationException($id, $e);
			}	
		}
		
		$compatibilityTest = new CompatibilityTest($entityProperty, $propertyAccessProxy);
		$scriptField->checkCompatibility($compatibilityTest);
		if ($compatibilityTest->hasFailed()) {
			throw ScriptManager::createInvalidScriptFieldConfigurationException($id, $compatibilityTest->getException());
		}
		
// @todo where to check			
// 			if ($scriptField instanceof Editable && $scriptField->isOptional()
// 			&& $propertyAccessProxy->getConstraints() !== null
// 			&& !$propertyAccessProxy->getConstraints()->allowsNull()) {
// 				throw ScriptManager::createInvalidScriptFieldConfigurationException($id, null,
// 						'ScriptField is optional but property setter method does not allow null.');
// 			}
// 		}
		
		return $scriptField;
	}
	
	public function createScriptCommand(EntityScript $entityScript, ScriptElementExtraction $configurableExtraction) {
		$scriptCommandClass = null;
		try {
			$scriptCommandClass = ReflectionUtils::createReflectionClass($configurableExtraction->getClassName());
		} catch (TypeNotFoundException $e) {
			throw ScriptManager::createInvalidScriptCommandConfigurationException(
					$configurableExtraction->getClassName(), $e);
		}
		
		if (!$scriptCommandClass->implementsInterface('rocket\script\entity\command\IndependentScriptCommand')) {
			throw ScriptManager::createInvalidScriptCommandConfigurationException($scriptCommandClass->getName(), null,
					'\'' . $scriptCommandClass->getName() . '\' must implement \'rocket\script\entity\command\IndependentScriptCommand\'.');
		}
		
		$scriptCommand = $scriptCommandClass->newInstance(new Attributes($configurableExtraction->getProps()));
		$scriptCommand->setEntityScript($entityScript);
		return $scriptCommand;
	}
	
	public function createScriptModificator(EntityScript $entityScript, ScriptElementExtraction $configurableExtraction) {
		$scriptModificatorClass = null;
		try {
			$scriptModificatorClass = ReflectionUtils::createReflectionClass($configurableExtraction->getClassName());
		} catch (TypeNotFoundException $e) {
			throw ScriptManager::createInvalidScriptModificatorConfigurationException(
					$configurableExtraction->getClassName(), $e);
		}
		
		if (!$scriptModificatorClass->implementsInterface('rocket\script\entity\modificator\IndependentScriptModificator')) {
			throw ScriptManager::createInvalidScriptCommandConfigurationException($scriptModificatorClass->getName(), null,
					'\'' . $scriptModificatorClass->getName() . '\' must implement \'rocket\script\entity\modificator\IndependentScriptModificator\'.');
		}
		
		$scriptModificator =  $scriptModificatorClass->newInstance(new Attributes($configurableExtraction->getProps()));
		$scriptModificator->setEntityScript($entityScript);
		return $scriptModificator;
	}
	
	public function createScriptListener(EntityScript $entityScript, ScriptElementExtraction $configurableExtraction) {
		$listenerClass = null;
		try {
			$listenerClass = ReflectionUtils::createReflectionClass($configurableExtraction->getClassName());
		} catch (TypeNotFoundException $e) {
			throw ScriptManager::createInvalidScriptListenerConfigurationException(
					$configurableExtraction->getClassName(), $e);
		}
		
		if (!$listenerClass->implementsInterface('rocket\script\entity\listener\IndependentScriptListener')) {
			throw ScriptManager::createInvalidScriptCommandConfigurationException($listenerClass->getName(), null,
					'\'' . $listenerClass->getName() . '\' must implement \'rocket\script\entity\listener\IndependentScriptListener\'.');
		}
		
		$scriptListener = $listenerClass->newInstance(new Attributes($configurableExtraction->getProps()));
		$scriptListener->setEntityScript($entityScript);
		return $scriptListener;
	}
	
}