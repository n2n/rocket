<?php
namespace rocket\script\entity\field\impl;

use n2n\persistence\Pdo;
use n2n\persistence\orm\store\RemovingJob;
use n2n\persistence\orm\store\PersistingJob;
use n2n\persistence\orm\store\MappingJob;
use rocket\script\entity\field\impl\EditableScriptFieldAdapter;
use rocket\script\entity\field\DraftableScriptField;
use n2n\core\DynamicTextCollection;
use n2n\dispatch\option\impl\BooleanOption;
use n2n\dispatch\option\OptionCollection;
use rocket\script\entity\adaptive\draft\Draftable;

abstract class DraftableScriptFieldAdapter extends EditableScriptFieldAdapter implements DraftableScriptField, Draftable {
	const OPTION_DRAFT_ENABLED_KEY = 'draft';	
	
	protected $draftEnabledDefault = false;
	
	public function isDraftEnabled() {
		return $this->attributes->get(self::OPTION_DRAFT_ENABLED_KEY, 
				$this->draftEnabledDefault);
	}
	
	public function setDraftEnabled($draftEnabled) {
		$this->attributes->set(self::OPTION_DRAFT_ENABLED_KEY, (boolean) $draftEnabled);
	}

	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		$this->applyDraftOptions($optionCollection);
		return $optionCollection;
	}
	
	protected function applyDraftOptions(OptionCollection $optionCollection) {
		$dtc = new DynamicTextCollection('rocket');
		$optionCollection->addOption(self::OPTION_DRAFT_ENABLED_KEY,
				new BooleanOption($dtc->translate('script_impl_draftable_label'), 
						$this->draftEnabledDefault));
	}
	
	public function getDraftable() {
		return $this;
	}
	
	public function getDraftColumnName() {
		return $this->getEntityProperty()->getReferencedColumnName();
	}
	
	public function checkDraftMeta(Pdo $dbh) {
	}
	
	public function draftCopy($value) {
		return $value;
	}
	
	public function publishCopy($value) {
		return $value;
	}
	
	public function mapDraftValue($draftId, MappingJob $mappingJob, \ArrayObject $rawDataMap, \ArrayObject $mappedValues) {
		$this->getEntityProperty()->mapValue($mappingJob, $rawDataMap, $mappedValues);
	}
	
	public function supplyDraftPersistingJob($mappedValue, PersistingJob $persistingJob) {
		$this->getEntityProperty()->supplyPersistingJob($mappedValue, $persistingJob);
	}
	
	public function supplyDraftRemovingJob($mappedValue, RemovingJob $deletingJob) {
		$this->getEntityProperty()->supplyRemovingJob($mappedValue, $deletingJob);
	}
}