<?php

namespace rocket\script\entity\field\impl\relation;

use n2n\persistence\orm\property\EntityProperty;
use n2n\ui\html\HtmlView;
use n2n\util\Attributes;
use n2n\reflection\ArgumentUtils;
use n2n\persistence\Pdo;
use rocket\script\entity\adaptive\draft\DraftMetaProvider;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\orm\store\MappingJob;
use n2n\persistence\orm\store\PersistingJob;
use n2n\persistence\orm\property\relation\JoinTableToManyRelation;
use n2n\persistence\orm\store\RemovingJob;
use n2n\core\IllegalStateException;
use n2n\persistence\orm\property\OneToOneProperty;
use n2n\core\NotYetImplementedException;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\EntryManageUtils;
use rocket\script\entity\adaptive\translation\TranslationPersistingActionQueue;
use rocket\script\entity\field\impl\relation\option\ToOneOption;
use rocket\script\entity\field\TranslatableScriptField;
use rocket\script\entity\field\DraftableScriptField;
use rocket\script\entity\adaptive\translation\TranslationMappingJob;
use rocket\script\entity\field\impl\relation\model\EmbeddedScriptFieldRelation;
use rocket\script\entity\adaptive\translation\TranslationRemovingActionQueue;
use rocket\script\entity\adaptive\translation\TranslationManager;
use rocket\script\entity\adaptive\translation\Translation;
use n2n\l10n\Locale;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\adaptive\draft\Draftable;
use rocket\script\entity\adaptive\translation\Translatable;
use n2n\dispatch\option\impl\BooleanOption;
use rocket\script\entity\field\impl\ManageInfo;

class EmbeddedOneToOneScriptField extends RelationScriptFieldAdapter implements DraftableScriptField, Draftable,
		 TranslatableScriptField, Translatable {
	const OPTION_REPLACEABLE_KEY = 'replaceable';
	const OPTION_REPLACEABLE_DEFAULT = false;
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		
		$this->initilaize(new EmbeddedScriptFieldRelation($this, false, false));
	}
	
	public function getTypeName() {
		return 'EmbeddedOneToOne';
	}
	
	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		$optionCollection->addOption(self::OPTION_REPLACEABLE_KEY, 
				new BooleanOption('Replaceable', self::OPTION_REPLACEABLE_DEFAULT));
		return $optionCollection;
	}
	
	public function isReplaceable() {
		return $this->getAttributes()->get(self::OPTION_REPLACEABLE_KEY, self::OPTION_REPLACEABLE_DEFAULT);
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof OneToOneProperty;
	}
	
	private function createTargetScriptSelection(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping) {
		$value = $scriptSelectionMapping->getValue($this->id);
		if ($value === null) return null;
		
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$targetEntity = null;
		$translatedTargetEntity = null;
		
		if (!$scriptSelection->hasTranslation()) {
			$targetEntity = $value;
		} else {
			if ($scriptSelection->hasDraft()) {
				$targetEntity = $scriptSelectionMapping->getDraftedValue($this->id);
			} else {
				$targetEntity = $scriptSelectionMapping->getOrgValue($this->id);
			}
			
			if ($this->isTranslationEnabled()) {
				$translatedTargetEntity = $value;
			}
		}
		
		$targetScriptSelection = new ScriptSelection($this->fieldRelation->getTargetEntityScript()
				->extractId($targetEntity), $targetEntity);
		
		if ($translatedTargetEntity !== null) {
			$targetScriptSelection->setTranslation($scriptState
					->getTranslationManager()->getManagedByTranslatedEntity($translatedTargetEntity));
		}
		
		return $targetScriptSelection;
	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$scriptState = $manageInfo->getScriptState();
		$targetUtils = new EntryManageUtils($this->fieldRelation->createTargetPseudoScriptState(
				$scriptState, $scriptSelectionMapping->getScriptSelection(), true));
		$toOneOption = new ToOneOption($this->getId(), $this->getLabel(), $scriptSelectionMapping, 
				$targetUtils, $this->isRequired($scriptSelectionMapping, $manageInfo));
		
		$targetScriptSelection = $this->createTargetScriptSelection($scriptState, $scriptSelectionMapping);
		$toOneOption->setTargetScriptSelection($targetScriptSelection);
		$toOneOption->setEmbeddedEditEnabled(true);
		if (!$scriptSelectionMapping->getScriptSelection()->hasTranslation()) {
			$toOneOption->setEmbeddedAddEnabled($targetScriptSelection === null || $this->isReplaceable());
			$toOneOption->setEmbeddedUnsetAllowed(true);
			$toOneOption->setRemoveUnusedEnabled(true);
		}
		return $toOneOption;
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view, ManageInfo $manageInfo) {
		$scriptState = $manageInfo->getScriptState();
		$targetScriptSelection = $this->createTargetScriptSelection($scriptState, $scriptSelectionMapping);
		
		if ($targetScriptSelection === null) return null;
		
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$targetEntityScript = $this->fieldRelation->getTargetEntityScript();
		$targetScriptState = $this->fieldRelation->createTargetPseudoScriptState(
				$scriptState, $scriptSelection, false);
		$targetUtils = new EntryManageUtils($targetScriptState);
		
		$targetScriptSelectionMapping = $targetUtils->createScriptSelectionMapping($targetScriptSelection);
		
		$entryInfo = $targetUtils->createEntryInfo($targetScriptSelectionMapping);
		$view = $entryInfo->getDisplayDefinition()->getScriptMask()->createEntryView($entryInfo);
		
		return $view->getImport($view);
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
		throw new NotYetImplementedException();
	}
	
	public function supplyDraftPersistingJob($mappedValue, PersistingJob $persistingJob) {
		throw new NotYetImplementedException();
	}
	
	public function supplyDraftRemovingJob($mappedValue, RemovingJob $deletingJob) {
		throw new NotYetImplementedException();
	}

	public function getTranslatable() {
		return $this;
	}
	
	public function mapTranslationValue(TranslationMappingJob $translationMappingJob, \ArrayObject $rawDataMap, \ArrayObject $mappedValues) {
		$that = $this; 
		$translationMappingJob->getMappingJob()->executeAtEnd(function() use ($that, $mappedValues, $translationMappingJob) {
			$entityProperty = $that->getEntityProperty();
			$targetBaseEntity = $entityProperty->getAccessProxy()->getValue($translationMappingJob->getBaseEntity());
			if ($targetBaseEntity !== null) {
				$mappedValues[$entityProperty->getName()] = $translationMappingJob->getTranslationManager()->find($targetBaseEntity,
						$translationMappingJob->getLocale(), true)->getTranslatedEntity();
			}
		});
	}

	public function supplyTranslationPersistingJob($mappedValue, PersistingJob $persistingJob, TranslationPersistingActionQueue $tpaq) {
// 		if ($mappedValue !== null) {
//  			$tpaq->getOrCreateTranslationPersistingJob($mappedValue);
// 		}
	}

	public function supplyTranslationRemovingJob($mappedValue, RemovingJob $removingJob, TranslationRemovingActionQueue $tpaq) {
		if ($mappedValue !== null) {
			$tpaq->getOrCreateTranslationRemovingJob($mappedValue);
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
	
	public function checkTranslationMeta(Pdo $dbh) { }
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\DraftableScriptField::isDraftEnabled()
	 */
	public function isDraftEnabled() {
		$this->fieldRelation->isDraftEnabled();	
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
		if ($value === null) return null;
		
		return $translationManager->createFromEntity($value, $locale)->getTranslatedEntity();
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
	
}