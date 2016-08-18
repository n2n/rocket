<?php
namespace rocket\script\entity\adaptive\translation;

use n2n\persistence\orm\store\PersistingJob;
use n2n\persistence\orm\store\RemovingJob;
use n2n\l10n\Locale;

interface Translatable {
	/**
	 * @return EntityProperty can return null
	 */				
	public function getTranslationEntityProperty();
	public function translationCopy($value, Locale $locale, TranslationManager $translationManager, $sourceTranslation);
	public function translationRead(Translation $translation);
	public function translationWrite(Translation $translation, $value);
	public function mapTranslationValue(TranslationMappingJob $translationMappingJob, \ArrayObject $rawDataMap, \ArrayObject $mappedValues);
	public function supplyTranslationPersistingJob($mappedValue, PersistingJob $persistingJob, TranslationPersistingActionQueue $tpaq);
	public function supplyTranslationRemovingJob($mappedValue, RemovingJob $removingJob, TranslationRemovingActionQueue $tpaq);
}