<?php
namespace rocket\script\entity\adaptive\translation;

use n2n\core\IllegalStateException;

use n2n\persistence\orm\OrmUtils;

use n2n\persistence\orm\Entity;

use n2n\l10n\Locale;

class Translation {
	private $id;
	private $locale;
	private $elementId;
	private $translatedEntity;
	
	public function __construct($id, Locale $locale, $elementId, Entity $translatedEntity) {
		$this->id = $id;
		$this->locale = $locale;
		$this->elementId = $elementId;
		$this->translatedEntity = $translatedEntity;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getLocale() {
		return $this->locale;
	}
	
	public function getElementId() {
		return $this->elementId;
	}
	
	public function getTranslatedEntity() {
		if (is_null($this->translatedEntity)) {
			throw new IllegalStateException('Translated entry not initialized.');
		}
		return $this->translatedEntity;
	}
	
	public function setTranslatedEntity(Entity $translatedEntity) {
		$this->translatedEntity = $translatedEntity;
	}
		
	public function getTranslatedRawDataMap() {
		return $this->translatedRawDataMap;
	}
	
	public function setTranslatedRawDataMap(\ArrayObject $translatedRawDataMap) {
		$this->translatedRawDataMap = $translatedRawDataMap;
	}
	
	public function copy($elementId) {
		return new Translation(null, $this->getLocale(), $elementId, OrmUtils::copy($this->getTranslatedEntity()));
	}
	
	public function equals($obj) {
		return $obj instanceof Translation && $this->getElementId() == $obj->getElementId() 
				&& $this->getLocale()->equals($obj->getLocale());
	}
}