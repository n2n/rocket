<?php
namespace rocket\script\entity\manage\mapping;

use n2n\persistence\orm\Entity;
use rocket\script\entity\adaptive\draft\Draft;
use n2n\reflection\property\TypeConstraints;
use rocket\script\entity\adaptive\draft\Draftable;
use rocket\script\entity\adaptive\translation\Translatable;
use rocket\script\entity\adaptive\translation\Translation;
use rocket\script\entity\adaptive\translation\TranslationManager;

class Mappable {
	private $typeConstraints;
	private $readable;
	private $writable;
	private $draftable;
	private $translatable;
	
	public function __construct(TypeConstraints $typeConstraint = null, Readable $readable = null, 
			Writable $writable = null, Draftable $draftable = null, Translatable $translatable = null) {
		$this->typeConstraints = $typeConstraint;
		$this->readable = $readable;
		$this->writable = $writable;
		$this->draftable = $draftable;
		$this->translatable = $translatable;
	}
	
// 	public function getTypeConstraints() {
// 		return $this->typeConstraints;
// 	}

// 	public function setTypeConstraints(TypeConstraints $typeConstraints) {
// 		$this->typeConstraints = $typeConstraints;
// 	}

// 	public function getReadable() {
// 		return $this->readable;
// 	}

// 	public function setReadable(Readable $readable = null) {
// 		$this->readable = $readable;
// 	}

// 	public function getWritable() {
// 		return $this->writable;
// 	}

// 	public function setWritable(Writable $writable = null) {
// 		$this->writable = $writable;
// 	}

// 	public function getDraftable() {
// 		return $this->draftable;
// 	}

// 	public function setDraftable(Draftable $draftable = null) {
// 		$this->draftable = $draftable;
// 	}

// 	public function getTranslatable() {
// 		return $this->translatable;
// 	}

// 	public function setTranslatable(Translatable $translatable = null) {
// 		$this->translatable = $translatable;
// 	}

	public function validateValue($value) {
		if ($this->typeConstraints !== null) {
			$this->typeConstraints->validate($value);
		}
	}
	
	public function isReadable() {
		return isset($this->readable);
	}

	public function read(Entity $entity) {
		if (null !== $this->readable) {
			return $this->readable->read($entity);
		}
		
		throw new MappingOperationFailedException('Mappable is not readable');
	}
	
	public function isWritable() {
		return isset($this->writable);
	}
	
	public function write(Entity $entity, $value) {
		if (null !== $this->writable) {
			$this->writable->write($entity, $value);
			return;
		}
		
		throw new MappingOperationFailedException('Mappable is not writable');
	}
	
	public function isDraftable() {
		return isset($this->draftable);
	}
	
	private function ensureDraftable() {
		if ($this->isDraftable()) return;
		
		throw new MappingOperationFailedException('Mappable is not draftable'); 
	}
	
	public function draftRead(Draft $draft) {
		$this->ensureDraftable();
		return $this->draftable->draftRead($draft);
	}
	
	public function draftWrite(Draft $draft, $value) {
		$this->ensureDraftable();
		return $this->draftable->draftWrite($draft, $value);
	}
	
	public function isTranslatable() {
		return isset($this->translatable);
	}
	
	private function ensureTranslatable() {
		if ($this->isTranslatable()) return;
		
		throw new MappingOperationFailedException('Mappable is not translatable'); 
	}
	
	public function translationCopy($value, $locale, TranslationManager $translationManager, $sourceTranslation) {
		$this->ensureTranslatable();
		
		return $this->translatable->translationCopy($value, $locale, $translationManager, $sourceTranslation);
	}
	
	public function translationRead(Translation $translation) {
		$this->ensureTranslatable();
		return $this->translatable->translationRead($translation);
	}
	
	public function translationWrite(Translation $translation, $value) {
		$this->ensureTranslatable();
		return $this->translatable->translationWrite($translation, $value);
	}	
}