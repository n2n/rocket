<?php
namespace rocket\script\entity\manage;

use rocket\script\entity\adaptive\translation\Translation;
use rocket\script\entity\adaptive\draft\Draft;
use n2n\persistence\orm\Entity;
use rocket\script\entity\command\ScriptCommand;
use rocket\script\entity\field\ScriptField;
use n2n\reflection\ReflectionUtils;

class ScriptSelection {
	const TYPE_ORIGINAL = 1;
	const TYPE_DRAFT = 2;
	const TYPE_TRANSLATION = 4;
	
	private $id;
	private $entity;
	private $draft;
	private $translation;
	
	public function __construct($id, Entity $entity, Draft $draft = null, Translation $translation = null) {
		$this->id = $id;
		$this->entity = $entity;
		$this->draft = $draft;
		$this->translation = $translation;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function isNew()  {
		return $this->id === null;
	}
	
	public function getOriginalEntity() {
		return $this->entity;
	}
	
	public function setOriginalEntity(Entity $entity) {
		$this->entity;
	}
	
	public function hasDraft() {
		return isset($this->draft);
	}
	/**
	 * @return Draft
	 */
	public function getDraft() {
		return $this->draft;
	}
	
	public function getDraftId() {
		if ($this->hasDraft()) {
			return $this->draft->getId();
		}
		return null;
	}
	
	public function setDraft(Draft $draft = null) {
		$this->draft = $draft;
	}
	
	public function hasTranslation() {
		return isset($this->translation);
	}
	
	public function getTranslation() {
		return $this->translation;
	}
	
	public function getTranslationLocaleId() {
		if ($this->hasTranslation()) {
			return $this->translation->getLocale()->getId();
		}
		return null;
	}
	
	public function setTranslation(Translation $translation = null) {
		$this->translation = $translation;
	}
	
	public function getTranslationLocale() {
		if (isset($this->translation)) {
			return $this->translation->getLocale();
		}
		
		return null;
	}
	
	public function getEntity() {
		if (isset($this->translation)) {
			return $this->translation->getTranslatedEntity();
		}
		
		if (isset($this->draft)) {
			return $this->draft->getDraftedEntity();
		}
		
		return $this->entity;
	}
	/**
	 * @deprecated use {@see ScriptSelection::getEntity}
	 * @return Entity
	 */
	public function getCurrentEntity() {
		return $this->getEntity();
	}
	
	public function getType() {
		if (isset($this->translation)) {
			return self::TYPE_TRANSLATION;
		}
		
		if (isset($this->draft)) {
			return self::TYPE_DRAFT;
		}
		
		return self::TYPE_ORIGINAL;
	}
	
	public function toNavPoint($previewType = null) {
		return new ScriptNavPoint($this->getId(), $this->getDraftId(), $this->getTranslationLocale(), $previewType);
	}
	
	public function equals($obj) {
		return $obj instanceof ScriptSelection && $this->id == $obj->getId()
				&& $this->getDraftId() == $obj->getDraftId() 
				&& $this->getTranslationLocaleId() == $obj->getTranslationLocaleId();
	}
}