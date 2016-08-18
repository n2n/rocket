<?php
namespace rocket\script\entity\manage\mapping;

use n2n\persistence\orm\Entity;
use rocket\script\entity\adaptive\translation\Translation;
use n2n\l10n\Locale;
use rocket\script\entity\adaptive\translation\TranslationManager;

class MappingDefinition {
	private $mappables = array();
	
	public function getIds() {
		return array_keys($this->mappables);
	}
	
	public function putMappable($id, Mappable $mappable) {
		$this->mappables[$id] = $mappable;
	}

	public function removeMappableById($id) {
		unset($this->mappables[$id]);
	}
	
	public function containsId($id) {
		return isset($this->mappables[$id]);
	}
	
	public function getMappableById($id) {
		if (!isset($this->mappables[$id])) {
			throw new MappingOperationFailedException('No Mappable for id \'' . $id . '\' defined.');
		}
		
		return $this->mappables[$id];
	}
	
	public function getMappables() {
		return $this->mappables;
	}
	
	public function readAll(Entity $entity) {
		$values = array();
		foreach ($this->mappables as $id => $mappable) {
			if ($mappable->isReadable()) {
				$values[$id] = $mappable->read($entity);
			}
		}
		return $values;
	}
	
	public function writeAll(Entity $entity, array $values) {
		foreach ($this->mappables as $id => $mappable) {
			if (array_key_exists($id, $values) && $mappable->isWritable()) {
				$mappable->write($entity, $values[$id]);
			}
		}
	}
	
	public function translationReadAll(Translation $translation, $draftableOnly = false) {
		$values = array();
		foreach ($this->mappables as $id => $mappable) {
			if (!$mappable->isTranslatable() || 
					($draftableOnly && !$mappable->isDraftable())) {
				continue;
			}
			
			$values[$id] = $mappable->translationRead($translation);
		}
		
		return $values;
	}
	
	public function translationCopyAll(array $values, Locale $locale, TranslationManager $translationManager, $sourceTranslation) {
		$copiedValues = array();
		foreach ($this->mappables as $id => $mappable) {
			if (!array_key_exists($id, $values) || !$mappable->isTranslatable()) continue;
				
			$copiedValues[$id] = $mappable->translationCopy($values[$id], $locale, $translationManager, $sourceTranslation);
		}
	
		return $copiedValues;
	}

	public function translationWriteAll(Translation $translation, array $values, $draftableOnly = false) {
		foreach ($this->mappables as $id => $mappable) {
			if (!array_key_exists($id, $values) || !$mappable->isTranslatable() || 
					($draftableOnly && !$mappable->isDraftable())) {
				continue;
			}
			
			$mappable->translationWrite($translation, $values[$id]);
		}
	}
	
// 	public function translationRead(Translation $translation, $id) {
// 		if (!isset($this->translatables[$id])) {
// 			throw new IllegalStateException('ScriptField ' . $id . ' not translatable');
// 		}
		
// 		return $this->translatables[$id]->translationRead($translation, $id);
// 	}
	


	
}