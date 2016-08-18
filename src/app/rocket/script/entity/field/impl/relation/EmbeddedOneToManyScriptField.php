<?php

namespace rocket\script\entity\field\impl\relation;

use n2n\persistence\orm\property\OneToManyProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\manage\ScriptSelection;
use n2n\ui\html\HtmlView;
use n2n\util\Attributes;
use n2n\reflection\ArgumentUtils;
use n2n\persistence\Pdo;
use rocket\script\entity\adaptive\draft\DraftMetaProvider;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\orm\store\MappingJob;
use n2n\persistence\orm\property\relation\JoinTableToManyLoader;
use n2n\persistence\orm\FetchType;
use n2n\persistence\orm\property\relation\ArrayObjectProxy;
use n2n\persistence\orm\store\PersistingJob;
use n2n\persistence\orm\property\relation\JoinTableRelationActionJob;
use n2n\persistence\orm\property\relation\JoinTableToManyRelation;
use n2n\persistence\orm\store\RemovingJob;
use n2n\persistence\orm\CascadeType;
use n2n\core\IllegalStateException;
use n2n\dispatch\option\impl\IntegerOption;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\core\NotYetImplementedException;
use rocket\script\entity\manage\EntryManageUtils;
use rocket\script\entity\field\TranslatableScriptField;
use rocket\script\entity\field\impl\relation\option\ToManyOption;
use rocket\script\entity\field\DraftableScriptField;
use rocket\script\entity\field\impl\relation\model\EmbeddedScriptFieldRelation;
use rocket\script\entity\adaptive\translation\Translation;
use rocket\script\entity\adaptive\translation\TranslationMappingJob;
use rocket\script\entity\adaptive\translation\TranslationPersistingActionQueue;
use rocket\script\entity\adaptive\translation\TranslationRemovingActionQueue;
use rocket\script\entity\adaptive\translation\TranslationManager;
use n2n\l10n\Locale;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\adaptive\draft\Draftable;
use rocket\script\entity\adaptive\translation\Translatable;
use rocket\script\entity\field\impl\ManageInfo;

class EmbeddedOneToManyScriptField extends RelationScriptFieldAdapter implements DraftableScriptField, Draftable,
		TranslatableScriptField, Translatable {
	const OPTION_MAX = 'max';
	const OPTION_MIN = 'min';
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		
		$this->initilaize(new EmbeddedScriptFieldRelation($this, false, true));
	}
	
	public function getTypeName() {
		return 'EmbeddedOneToMany';
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof OneToManyProperty;
	}
	
	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();

		$optionCollection->addOption(self::OPTION_MIN, new IntegerOption('Min'));
		$optionCollection->addOption(self::OPTION_MAX, new IntegerOption('Max'));
		
		return $optionCollection;
	}
	
	public function getMin() {
		if (null !== ($min = $this->attributes->get(self::OPTION_MIN))) {
			return $min;
		}
		
		return (int) $this->isMandatory();
	}
	
	public function getMax() {
		return $this->attributes->get(self::OPTION_MAX);
	}	
	
	private function createTargetScriptSelections(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping) {
		$values = $scriptSelectionMapping->getValue($this->id);
		if ($values === null) return array();
	
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$targetEntities = null;
		$targetTranslatedEntities = null;
		
		if (!$scriptSelection->hasTranslation()) {
			$targetEntities = $values;
		} else {
			if ($scriptSelection->hasDraft()) {
				$targetEntities = $scriptSelectionMapping->getDraftedValue($this->id);
			} else {
				$targetEntities = $scriptSelectionMapping->getOrgValue($this->id);
			}
			if ($this->isTranslationEnabled()) {
				$targetTranslatedEntities = $values;
			}
		}
		
		$translationManager = $scriptState->getTranslationManager();
		$targetEntityScript = $this->fieldRelation->getTargetEntityScript();
		$targetScriptSelections = array();
		foreach ($targetEntities as $key => $targetEntity) {
			$targetScriptSelection = new ScriptSelection($targetEntityScript->extractId($targetEntity), $targetEntity);
			
			if ($targetTranslatedEntities !== null) {
				if (!isset($targetTranslatedEntities[$key])) {
					throw new IllegalStateException('Translation missmatch');
				}
				$targetScriptSelection->setTranslation($translationManager->getManagedByTranslatedEntity(
						$targetTranslatedEntities[$key]));
			}				

			$targetScriptSelections[$key] = $targetScriptSelection;
		}
		
		return $targetScriptSelections;
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view, ManageInfo $manageInfo) {
		$scriptState = $manageInfo->getScriptState();
		$targetScriptSelections = $this->createTargetScriptSelections($scriptState, $scriptSelectionMapping);
		if (!sizeof($targetScriptSelections)) return null;
		
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$targetEntityScript = $this->fieldRelation->getTargetEntityScript();
		$targetScriptState = $this->fieldRelation->createTargetPseudoScriptState($scriptState, $scriptSelection, false);
		$targetUtils = new EntryManageUtils($targetScriptState);
		
		$entryViews = array();
		foreach ($targetScriptSelections as $targetScriptSelection) {
			$mapping = $targetUtils->createScriptSelectionMapping($targetScriptSelection);
			$entryInfo = $targetUtils->createEntryInfo($mapping);
			$entryViews[] = $entryInfo->getDisplayDefinition()->getScriptMask()->createEntryView($entryInfo);
		}
		
		return $view->getImport('script\entity\field\impl\relation\view\embeddedOneToMany.html',
				array('entryViews' => $entryViews));
	}
	
	const DEFAULT_ADDABLES_NUM = 6;
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$scriptState = $manageInfo->getScriptState();
		$targetUtils = new EntryManageUtils($this->fieldRelation->createTargetPseudoScriptState(
				$scriptState, $scriptSelectionMapping->getScriptSelection(), true));
		$toManyOption = new ToManyOption($this->getId(), $this->getLabel(), $scriptSelectionMapping, 
				$targetUtils, $this->getMin(), $this->getMax());

		$toManyOption->setEmbeddedEditEnabled(true);
		
		if (!$scriptSelectionMapping->getScriptSelection()->hasTranslation()) {
			$toManyOption->setEmbeddedUnsetAllowed(true);
			$toManyOption->setRemoveUnusedEnabled(true);
			
			if (null !== ($max = $this->getMax())) {
				$toManyOption->setEmbeddedAddablesNum($max); 
			} else {
				$num = $this->getMin();
				if ($num === null || $num < self::DEFAULT_ADDABLES_NUM) {
					$num = self::DEFAULT_ADDABLES_NUM;
				}
				
				$toManyOption->setEmbeddedAddablesNum($num);
			}
		}
		
		$toManyOption->setTargetScriptSelections($this->createTargetScriptSelections($scriptState, $scriptSelectionMapping));
		return $toManyOption;
	}
			
	public function checkDraftMeta(Pdo $dbh) {
		$toManyRelation = $this->getEntityProperty()->getRelation();
		IllegalStateException::assertTrue($toManyRelation instanceof JoinTableToManyRelation);
		
		$database = $dbh->getMetaData()->getDatabase();
		$joinTableName = $toManyRelation->getJoinTableName();
		$joinTable = $database->getMetaEntityByName($joinTableName);
		ArgumentUtils::assertTrue($joinTable instanceof Table);
		
		$darftJoinTableName = DraftMetaProvider::createDraftTableName($joinTableName);
		if ($database->containsMetaEntityName($darftJoinTableName)) {
			if (!$this->checkDraftJoinTable($joinTable, $draftJoinTable)) {
				$database->removeMetaEntityByName($draftJoinTable->getName());
			}
		}

		if (!$database->containsMetaEntityName($darftJoinTableName)) {
			$database->addMetaEntity($database->getMetaEntityByName($joinTableName)
					->copy($darftJoinTableName));
		}
	}
	
	private function checkDraftJoinTable(Table $joinTable, MetaEntity $draftJoinTable) {
		if (!($draftJoinTable instanceof Table)) return false;
		
		foreach ($joinTable->getColumns() as $column) {
			if (!$draftJoinTable->containsColumnName($column->getName())) return false;
			
			if (!$column->equalsType($draftJoinTable->getColumnByName($column->getName()))) {
				$draftJoinTable->removeColumnByName($column->getName());
				$draftJoinTable->addColumn($column->copy());
			}
		}
		
		return true;
	}
	
	public function getDraftable() {
		return $this;
	}
	
	public function mapDraftValue($draftId, MappingJob $mappingJob, \ArrayObject $rawDataMap, \ArrayObject $mappedValues) {
		$entityModel = $this->getEntityProperty()->getEntityModel();
		$toManyRelation = $this->getEntityProperty()->getRelation();
		IllegalStateException::assertTrue($toManyRelation instanceof JoinTableToManyRelation);
		
		$em = $mappingJob->getPersistenceContext()->getEntityManager();
		
		$manyLoader = new JoinTableToManyLoader($em, $draftId, $toManyRelation->getTargetEntityModel(), 
				DraftMetaProvider::createDraftTableName($toManyRelation->getJoinTableName()), 
				$toManyRelation->getJoinColumnName(), $toManyRelation->getInverseJoinColumnName(), $toManyRelation->getRelationAnno());

		$that = $this;
		$mappingJob->executeAtEnd(function(/*MappingJob $mappingJob*/) use ($toManyRelation, $manyLoader, $that, $mappedValues) {
			$entityProperty = $that->getEntityProperty();
			if ($toManyRelation->getFetchType() == FetchType::EAGER) {
				$mappedValues[$entityProperty->getName()] = $manyLoader->load();
			} else {
				$mappedValues[$entityProperty->getName()] = new ArrayObjectProxy($manyLoader);
			}
		});
	}
	
	public function supplyDraftPersistingJob($mappedValue, PersistingJob $persistingJob) {
		$toManyRelation = $this->getEntityProperty()->getRelation();
		IllegalStateException::assertTrue($toManyRelation instanceof JoinTableToManyRelation);
		$actionQueue = $persistingJob->getPersistenceActionQueue();
		
		$relationActionJob = new JoinTableRelationActionJob($actionQueue, 
				DraftMetaProvider::createDraftTableName($toManyRelation->getJoinTableName()), 
				$toManyRelation->getJoinColumnName(), $toManyRelation->getInverseJoinColumnName());
		$relationActionJob->addDependent($persistingJob);
		$toManyRelation->applyTargetPeristingJobs($actionQueue, $mappedValue, $relationActionJob);
		
		if ($persistingJob->getPersistenceMeta()->hasId()) {
			$relationActionJob->setId($persistingJob->getPersistenceMeta()->getId());
			$actionQueue->add($relationActionJob);
			return;
		}
		
		$persistingJob->executeAtEnd(function(PersistingJob $persistingJob) use ($relationActionJob) {
			if (!$persistingJob->getPersistenceMeta()->hasId()) return;
			$relationActionJob->setId($persistingJob->getPersistenceMeta()->getId());
			$persistingJob->getPersistenceActionQueue()->add($relationActionJob);
		});
	}
	
	public function supplyDraftRemovingJob($mappedValue, RemovingJob $deletingJob) {
		$toManyRelation = $this->getEntityProperty()->getRelation();
		IllegalStateException::assertTrue($toManyRelation instanceof JoinTableToManyRelation);
		
		if (!$deletingJob->getRemoveMeta()->hasId()) {
			return;
		}
		
		$actionQueue = $deletingJob->getRemoveActionQueue();
		$relationActionJob = new JoinTableRelationActionJob($actionQueue, 
				DraftMetaProvider::createDraftTableName($toManyRelation->getJoinTableName()), 
				$toManyRelation->getJoinColumnName(), $toManyRelation->getInverseJoinColumnName());
		$relationActionJob->setId($deletingJob->getRemoveMeta()->getObjectId());
		$relationActionJob->addDependent($deletingJob);
		
		$actionQueue->add($relationActionJob);
		
		if ($mappedValue === null || !($toManyRelation->getCascadeType() & CascadeType::REMOVE)) return;
		
		$actionQueue = $deletingJob->getRemoveActionQueue();
		foreach ($mappedValue as $targetObject) {
			$actionQueue->getOrCreateRemovingJob($targetObject);
		}
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\TranslatableScriptField::isTranslationEnabled()
	 */
	public function isTranslationEnabled() {
		return $this->fieldRelation->isTranslationEnabled();
	}	
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\TranslatableScriptField::getTranslationColumnName()
	 */
	public function getTranslationColumnName() {
		return null;
	}
	
	public function getTranslatable() {
		return $this;
	}
	
	public function checkTranslationMeta(Pdo $dbh) { }
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\DraftableScriptField::isDraftEnabled()
	 */
	public function isDraftEnabled() {
		return $this->fieldRelation->isDraftEnabled();		
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\DraftableScriptField::getDraftColumnName()
	 */
	public function getDraftColumnName() {
		throw new NotYetImplementedException();
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\adaptive\translation\Translatable::translationCopy()
	 */
	public function translationCopy($value, Locale $locale, TranslationManager $translationManager, $sourceTranslation) {
		if ($value === null) return array();
		
		$copies = new \ArrayObject();
		foreach ($value as $key => $targetEntity) {
			$copies[$key] = $translationManager->createFromEntity($targetEntity, $locale)
					->getTranslatedEntity();
		}
		return $copies;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\adaptive\translation\Translatable::translationRead()
	 */
	public function translationRead(Translation $translation) {
		return $this->getPropertyAccessProxy()->getValue($translation->getTranslatedEntity());
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\adaptive\translation\Translatable::translationWrite()
	 */
	public function translationWrite(Translation $translation, $value) {
		$this->getPropertyAccessProxy()->setValue($translation->getTranslatedEntity(), $value);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\adaptive\translation\Translatable::getTranslationEntityProperty()
	 */
	public function getTranslationEntityProperty() {
		return $this->getEntityProperty();	
	}

	public function mapTranslationValue(TranslationMappingJob $translationMappingJob, \ArrayObject $rawDataMap, \ArrayObject $mappedValues) {
		$that = $this; 
		$translationMappingJob->getMappingJob()->executeAtEnd(function() use ($that, $mappedValues, $translationMappingJob) {
			$entityProperty = $that->getEntityProperty();
			$targetBaseEntities = $this->read($translationMappingJob->getBaseEntity());
			
			$targetTranslatedEntities = new \ArrayObject();
			
			if ($targetBaseEntities !== null) {
				foreach ($targetBaseEntities as $key => $targetBaseEntity) {
					$targetTranslatedEntities[$key] = $translationMappingJob->getTranslationManager()->find($targetBaseEntity,
							$translationMappingJob->getLocale(), true)->getTranslatedEntity();
				}
			}
			
			$mappedValues[$entityProperty->getName()] = $targetTranslatedEntities;
		});
	}

	public function supplyTranslationPersistingJob($mappedValue, PersistingJob $persistingJob, TranslationPersistingActionQueue $tpaq) {
// 		if ($mappedValue !== null) {
// 			foreach ($mappedValue as $targetTranslatedEntity) {
// 				$tpaq->getOrCreateTranslationPersistingJob($targetTranslatedEntity);
// 			}
// 		}
	}

	public function supplyTranslationRemovingJob($mappedValue, RemovingJob $removingJob, TranslationRemovingActionQueue $tpaq) {
		if ($mappedValue !== null) {
			foreach ($mappedValue as $targetTranslatedEntity) {
 				$tpaq->getOrCreateTranslationRemovingJob($targetTranslatedEntity);
			}
		}
	}

}