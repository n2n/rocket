<?php

namespace rocket\script\entity\field\impl\relation\model;

use rocket\script\entity\field\impl\relation\RelationScriptField;
use n2n\dispatch\option\impl\BooleanOption;
use rocket\script\core\SetupProcess;
use n2n\dispatch\option\OptionCollection;
use n2n\core\IllegalStateException;
use n2n\dispatch\option\impl\EnumOption;
use n2n\persistence\orm\property\RelationProperty;
use n2n\reflection\ArgumentUtils;
use rocket\script\core\UnknownScriptException;
use n2n\persistence\orm\property\relation\MasterRelation;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\field\impl\relation\command\RelationScriptCommand;
use rocket\script\entity\field\impl\relation\model\MappedOneToCriteriaFactory;
use rocket\script\entity\field\impl\relation\model\RelationCriteriaFactory;
use rocket\script\entity\modificator\impl\ScriptModificatorAdapter;
use n2n\persistence\orm\Entity;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\UnknownScriptMaskException;
use rocket\script\entity\UnknownScriptElementException;
use n2n\core\NotYetImplementedException;
use n2n\persistence\orm\property\relation\MappedRelation;
use rocket\script\core\ManageState;
use rocket\script\entity\manage\ScriptRelation;
use n2n\http\ControllerContext;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\script\entity\manage\mapping\WrittenMappingListener;
use n2n\persistence\orm\property\relation\JoinTableRelation;
use n2n\persistence\orm\CascadeType;

abstract class ScriptFieldRelation {
	protected $targetEntityScript;
	protected $targetMask;
	protected $targetMasterField;
	protected $relationProperty;
	
	protected $relationField;
	protected $sourceMany;
	protected $targetMany;	
	
	public function __construct(RelationScriptField $scriptField, $sourceMany, $targetMany) {
		$this->relationField = $scriptField;
		$this->sourceMany = (boolean) $sourceMany;
		$this->targetMany = (boolean) $targetMany;
	}
	
	public function getRelationField() {
		return $this->relationField;
	}
	
	public function isSourceMany() {
		return $this->sourceMany;
	}
	
	public function isTargetMany() {
		return $this->targetMany;
	}

	const OPTION_TARGET_MASK_KEY = 'targetMaskId';
	const OPTION_FILTERED_KEY = 'filtered';
	const OPTION_FILTERED_DEFAULT = true;
	
	public function completeOptionCollection(OptionCollection $optionCollection) {		
		$maskOptions = array();
		foreach ($this->getTargetEntityScript()->getMaskSet() as $mask) {
			$maskOptions[$mask->getId()] = $mask->getLabel();
		}
		$optionCollection->addOption(self::OPTION_TARGET_MASK_KEY, new EnumOption('Target Mask', $maskOptions));
		
		$optionCollection->addOption(self::OPTION_FILTERED_KEY, new BooleanOption('Filtered', self::OPTION_FILTERED_DEFAULT, false));
		return $optionCollection;
	}
	
	public function isFiltered() {
		return (boolean) $this->relationField->getAttributes()
				->get(self::OPTION_FILTERED_KEY, self::OPTION_FILTERED_DEFAULT);
	}
	
	public function hasRecursiveConflict(ScriptState $scriptState) {
		$targetEntityScript = $this->getTargetEntityScript();
		while (null !== ($scriptState = $scriptState->getParent())) {
			if ($scriptState->getContextEntityScript()->equals($targetEntityScript)) {
				return true;
			}
		}
		return false;
	}
	
	public function isReadOnlyRequired(ScriptSelectionMapping $mapping, ScriptState $scriptState) {
		// @todo remove when
		return ($this->isFiltered() && $scriptState->getScriptRelation($this->relationField->getId()));
	}
	
	protected function getTargetMaskId() {
		return $this->relationField->getAttributes()
				->get(self::OPTION_TARGET_MASK_KEY);
	}
	
	public function setup(SetupProcess $setupProcess) {
		$this->relationProperty = $this->relationField->getEntityProperty();
		ArgumentUtils::assertTrue($this->relationProperty instanceof RelationProperty);
		
		$targetEntityClass = $this->relationProperty->getRelation()->getTargetEntityModel()->getClass();
		$scriptManager = $setupProcess->getScriptManager();
		try {
			$this->targetEntityScript = $scriptManager->getEntityScriptByClass($targetEntityClass);
			if (null !== ($targetMaskId = $this->getTargetMaskId())) {
				$this->targetMask = $this->targetEntityScript->getMaskById($targetMaskId);
			} else {
				$this->targetMask = $this->targetEntityScript->getOrCreateDefaultMask();
			}
			
			if (!$this->isMaster()) {
				$this->targetMasterField = $this->targetEntityScript->getScriptFieldByEntityPropertyName(
						$this->relationProperty->getRelation()->getTargetEntityProperty()->getName());
				if (!($this->targetMasterField instanceof RelationScriptField)) {
					throw new NotYetImplementedException('Target ScriptField is no RelationScriptField. Exception not yet implemented. ' . get_class($this->targetMasterField));
				}
			}
		} catch (UnknownScriptException $e) {
			$setupProcess->failedE($this->relationField, $e);
			return;
		} catch (UnknownScriptMaskException $e) {
			$setupProcess->failedE($this->relationField, $e);
			return;
		} catch (UnknownScriptElementException $e) {
			$setupProcess->failed($this->relationField, 'ScriptField for Mapped Property required', $e);
			return;
		}
		
		$this->relationCommand = new RelationScriptCommand($this);
		$this->relationField->getEntityScript()->getTopEntityScript()->getCommandCollection()->add($this->relationCommand);
		// @todo could go wrong if source delete was cascaded from target	
		if (!$this->isMaster() && !$this->isRemoveCascaded()) {
			$this->relationField->getEntityScript()->getListenerCollection()->add(new MappedDeleteScriptListener(
					$this->relationField->getPropertyAccessProxy(), $this->getTargetMasterScriptField()->getPropertyAccessProxy(),
					$this->isSourceMany(), $this->isTargetMany()));
		}
	}
	
	public function findTargetScriptField() {
		if (null !== ($scriptField = $this->getTargetMasterScriptField())) {
			return $scriptField;
		}
		
		$targetEntityScript = $this->getTargetEntityScript();
	
		foreach ($targetEntityScript->getFieldCollection() as $targetScriptField) {
			if (!($targetScriptField instanceof RelationScriptField)) continue;
			
			$targetRelationProperty = $targetScriptField->getEntityProperty();
			
			$targetRelation = $targetRelationProperty->getRelation();
			if ($targetRelation instanceof MappedRelation
					&& $targetRelation->getTargetEntityProperty()->equals($this->relationProperty)) {
				return $targetScriptField;
			}
		}
	
		return null;
	}
	
	public function getRelationProperty() {
		if ($this->relationProperty === null) {
			throw new IllegalStateException(get_class($this->relationField) . 'not set up');
		}
		return $this->relationProperty;
	}
	/**
	 * @return \rocket\script\entity\EntityScript
	 */
	public function getTargetEntityScript() {
		if ($this->targetEntityScript === null) {
			throw new IllegalStateException(get_class($this->relationField) . 'not set up');
		}
		return $this->targetEntityScript;
	}
	
	public function getTargetMask() {
		if ($this->targetMask === null) {
			throw new IllegalStateException(get_class($this->relationField) . 'not set up');
		}
		return $this->targetMask;
	}
	
	public function getTargetMasterScriptField() {
		if ($this->targetMasterField === null && !$this->isMaster()) {
			throw new IllegalStateException(get_class($this->relationField) . ' not set up');
		}
		return $this->targetMasterField;
	}
	
	public function isMaster() {
		return $this->getRelationProperty()->getRelation() instanceof MasterRelation;
	}
		
	public function createTargetScriptState(ManageState $manageState, ScriptState $scriptState, ScriptSelection $scriptSelection, 
			ControllerContext $controllerContext) {
		$targetScriptState = $manageState->createScriptState($this->getTargetEntityScript(), $controllerContext);
		$this->configureTargetScriptState($targetScriptState, $scriptState, $scriptSelection);
		
		$this->getTargetEntityScript()->setupScriptState($targetScriptState);
		
		return $targetScriptState;
	}
	
	public function createTargetPseudoScriptState(ScriptState $scriptState, ScriptSelection $scriptSelection, $editCommandRequired) {
		$targetControllerContext = new ControllerContext();
		$targetContextCmds = $scriptState->getControllerContext()->getContextCmds();
		if (!$scriptSelection->isNew()) {
			$targetContextCmds[] = $this->relationCommand->getId();
			$targetContextCmds[] = $scriptSelection->getId();
		}
		$targetControllerContext->setContextCmds($targetContextCmds);
	
		$targetScriptState = $scriptState->createChildScriptState($this->getTargetEntityScript(), $targetControllerContext, true);
		$this->configureTargetScriptState($targetScriptState, $scriptState, $scriptSelection, $editCommandRequired);
		
		$this->getTargetEntityScript()->setupScriptState($targetScriptState);
		
		return $targetScriptState;
	}
	
	protected function configureTargetScriptState(ScriptState $targetScriptState, ScriptState $scriptState, 
			ScriptSelection $scriptSelection, $editCommandRequired = null) {
		if (null !== ($targetCriteriaFactory = $this->createTargetCriteriaFactory($scriptSelection))) {
			$targetScriptState->setCriteriaFactory($targetCriteriaFactory);
		}
		
		$targetScriptState->setScriptMask($this->targetMask);
		
		$this->applyTargetModificators($targetScriptState, $scriptState, $scriptSelection);
		
		return $targetScriptState;
	}
	
	protected function applyTargetModificators(ScriptState $targetScriptState, ScriptState $scriptState, 
			ScriptSelection $scriptSelection) {
		$targetScriptField = $this->findTargetScriptField();
		$targetModificatorCollection = $targetScriptState->getContextEntityScript()->getModificatorCollection();
		
		if (null !== $targetScriptField) {
			// @todo maybe include relationField
			$targetScriptState->setScriptRelation($targetScriptField->getId(), new ScriptRelation($scriptState, $scriptSelection, $this->relationField));
			$targetModificatorCollection->add(new MappedRelationScriptModificator($targetScriptState, $scriptSelection->getEntity(),
					$targetScriptField->getId(), $this->isSourceMany()));
		}
		
		
		
		if ($this->isMaster()) {
			$targetModificatorCollection->add(new MasterRelationScriptModificator($targetScriptState, $scriptSelection->getEntity(),
					$this->relationField->getPropertyAccessProxy(), $this->isTargetMany()));
		}
	}
	
	
	protected function createTargetCriteriaFactory(ScriptSelection $scriptSelection) {
		if ($scriptSelection->isNew()) return null;
		
		if (!$this->isMaster() && !$this->isSourceMany()) {
			return new MappedOneToCriteriaFactory($this->relationProperty->getRelation(), $scriptSelection->getOriginalEntity());	
		}
		
		return new RelationCriteriaFactory($this->relationProperty, $scriptSelection->getOriginalEntity());
	}
	
	private function writeTarget(Entity $targetValue, Entity $entity, $targetMasterScriptField) {
		if (!$this->isSourceMany()) {
			$targetMasterScriptField->getPropertyAccessProxy()->setValue($targetValue, $entity);
			return;
		}
		
		$sourceEntities = $targetMasterScriptField->getPropertyAccessProxy()->getValue($targetValue);
		if ($sourceEntities === null) {
			$sourceEntities = new \ArrayObject();
		}
		
		foreach ($sourceEntities as $sourceEntity) {
			if ($sourceEntity === $entity) return;
		}
	
		$sourceEntities[] = $entity;
		$targetMasterScriptField->getPropertyAccessProxy()->setValue($targetValue, $sourceEntities);
	
		return;
	}

	public function write(Entity $entity, $value) {
		$this->relationField->getPropertyAccessProxy()->setValue($entity, $value);

		if ($this->isMaster() || empty($value)) return;
		
		$targetMasterScriptField = $this->getTargetMasterScriptField();
		
		if (!$this->isTargetMany()) {
			$this->writeTarget($value, $entity, $targetMasterScriptField);
			return;
		}
		
		foreach ($value as $targetValue) {
			$this->writeTarget($targetValue, $entity, $targetMasterScriptField);
		}
	}
	
	public function isPersistCascaded() {
		return $this->getRelationProperty()->getRelation()->getCascadeType() & CascadeType::PERSIST;
	}
	
	public function isRemoveCascaded() {
		return $this->getRelationProperty()->getRelation()->getCascadeType() & CascadeType::REMOVE;
	}
	
	public function isJoinTableRelation() {
		return $this->getRelationProperty()->getRelation() instanceof JoinTableRelation;
	}
}

class MappedRelationScriptModificator extends ScriptModificatorAdapter {
	private $targetScriptState;
	private $entity;
	private $targetEditableId;
	private $sourceMany;

	public function __construct(ScriptState $targetScriptState, Entity $entity, $targetEditableId, $sourceMany) {
		$this->targetScriptState = $targetScriptState;
		$this->entity = $entity;
		$this->targetEditableId = $targetEditableId;
		$this->sourceMany = (boolean) $sourceMany;
	}
	
	public function setupScriptSelectionMapping(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping) {
		if ($this->targetScriptState !== $scriptState
				|| !$scriptSelectionMapping->getScriptSelection()->isNew()) return;

		if (!$this->sourceMany) {
			$scriptSelectionMapping->setValue($this->targetEditableId, $this->entity);
			return;
		}
		
		$value = $scriptSelectionMapping->getValue($this->targetEditableId);
		if ($value === null) {
			$value = new \ArrayObject();
		}
		$value[] = $this->entity;
		$scriptSelectionMapping->setValue($this->targetEditableId, $value);
	}
}

class MasterRelationScriptModificator extends ScriptModificatorAdapter {
	private $targetScriptState;
	private $entity;
	private $propertyAccessProxy;
	private $targetMany;

	public function __construct(ScriptState $targetScriptState, Entity $entity, PropertyAccessProxy $propertyAccessProxy, $targetMany) {
		$this->targetScriptState = $targetScriptState;
		$this->entity = $entity;
		$this->propertyAccessProxy = $propertyAccessProxy;
		$this->targetMany = (boolean) $targetMany;
	}

	public function setupScriptSelectionMapping(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping) {
		if ($this->targetScriptState !== $scriptState) return;
		
		$that = $this;
		if (!$this->targetMany) {
			$scriptSelectionMapping->registerListener(new WrittenMappingListener(
					function () use ($that, $scriptSelectionMapping) {
						$that->propertyAccessProxy->setValue($that->entity, $scriptSelectionMapping->getScriptSelection()->getEntity());
					}));
			return;
		} 
		
		$scriptSelectionMapping->registerListener(new WrittenMappingListener(
				function () use ($that, $scriptSelectionMapping) {
					$targetEntities = $that->propertyAccessProxy->getValue($that->entity);
					if ($targetEntities === null) {
						$targetEntities = new \ArrayObject();
					}
					$targetEntities[] = $scriptSelectionMapping->getScriptSelection()->getEntity();
					$that->propertyAccessProxy->setValue($that->entity, $targetEntities);
				}));
	}
}