<?php
namespace rocket\script\entity\field\impl;

use rocket\script\entity\field\TranslatableScriptField;
use n2n\persistence\orm\store\PersistingJob;
use n2n\persistence\orm\store\RemovingJob;
use n2n\persistence\Pdo;
use n2n\core\DynamicTextCollection;
use n2n\dispatch\option\impl\BooleanOption;
use n2n\dispatch\option\OptionCollection;
use rocket\script\entity\adaptive\translation\Translation;
use rocket\script\entity\adaptive\translation\TranslationMappingJob;
use rocket\script\entity\adaptive\translation\TranslationPersistingActionQueue;
use rocket\script\entity\adaptive\translation\TranslationRemovingActionQueue;
use rocket\script\entity\adaptive\translation\TranslationManager;
use n2n\l10n\Locale;
use rocket\script\entity\adaptive\translation\Translatable;

abstract class TranslatableScriptFieldAdapter extends DraftableScriptFieldAdapter implements TranslatableScriptField, Translatable {
	const OPTION_TRANSLATION_ENABLED_KEY = 'translation';
	protected $translationEnabledDefault = false;
	
	public function isTranslationEnabled() {
		return $this->attributes->get(self::OPTION_TRANSLATION_ENABLED_KEY, 
				$this->translationEnabledDefault);
	}
	
	public function setTranslationEnabled($translationEnabled) {
		$this->attributes->set(self::OPTION_TRANSLATION_ENABLED_KEY, (bool) $translationEnabled);
	}

	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		$this->applyTranslationOptions($optionCollection);
		return $optionCollection;
	}
	
	protected function applyTranslationOptions(OptionCollection $optionCollection) {
		$dtc = new DynamicTextCollection('rocket');
		$optionCollection->addOption(self::OPTION_TRANSLATION_ENABLED_KEY,
				new BooleanOption($dtc->translate('script_impl_translatable_label'), 
						$this->translationEnabledDefault));
	}
	
	public function getTranslationColumnName() {
		return $this->getEntityProperty()->getReferencedColumnName();
	}
	
	public function checkTranslationMeta(Pdo $dbh) {
	}
	
	public function getTranslationEntityProperty() {
		return $this->getEntityProperty();
	}
	
	public function getTranslatable() {
		return $this;
	}
	
	public function translationRead(Translation $translation) {
		return $this->read($translation->getTranslatedEntity());
	}
	
	public function translationWrite(Translation $translation, $value) {
		$this->write($translation->getTranslatedEntity(), $value);
	}
	
	public function translationCopy($value, Locale $locale, TranslationManager $translationManager, $sourceTranslation) {
		return $value;
	}
	
	public function mapTranslationValue(TranslationMappingJob $translationMappingJob, \ArrayObject $rawDataMap, \ArrayObject $mappedValues) {
		$this->getEntityProperty()->mapValue($translationMappingJob->getMappingJob(), $rawDataMap, $mappedValues);
	}
	
	public function supplyTranslationPersistingJob($mappedValue, PersistingJob $persistingJob, TranslationPersistingActionQueue $tpaq) {
		$this->getEntityProperty()->supplyPersistingJob($mappedValue, $persistingJob);
	}
	
	public function supplyTranslationRemovingJob($mappedValue, RemovingJob $removingJob, TranslationRemovingActionQueue $tpaq) {
		$this->getEntityProperty()->supplyRemovingJob($mappedValue, $removingJob);
	}
}