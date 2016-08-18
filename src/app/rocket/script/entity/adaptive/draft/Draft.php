<?php
namespace rocket\script\entity\adaptive\draft;

use n2n\core\IllegalStateException;

use n2n\persistence\orm\Entity;

class Draft {
	private $id;
	private $lastMod;
	private $published;
	private $draftedObjectId;
	private $draftedEntity;
	
	public function __construct($id, \DateTime $lastMod, $published, $draftedObjectId, Entity $draftedEntity) {
		$this->id = $id;
		$this->lastMod = $lastMod;
		$this->published = $published;
		$this->draftedObjectId = $draftedObjectId;
		$this->draftedEntity = $draftedEntity;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getLastMod() {
		return $this->lastMod;
	}
	
	public function setLastMod($lastMod) {
		$this->lastMod = $lastMod;
	}
	
	public function isPublished() {
		return $this->published;
	}
	
	public function setPublished($published) {
		$this->published = (boolean) $published;
	}
	
	public function getDraftedObjectId() {
		return $this->draftedObjectId;
	}
	
	public function setDraftedEntity(Entity $draftedEntity) {
		$this->draftedEntity = $draftedEntity;
	}
	
	public function getDraftedEntity() {
		if (null === $this->draftedEntity) {
			throw new IllegalStateException('Drafted entry not initialized');
		}
		
		return $this->draftedEntity;
	}
	
	public function equals($obj) {
		if (!($obj instanceof Draft)) return false;
		
		return $this->getId() == $obj->getId() && $this->getDraftedObjectId() == $obj->getDraftedObjectId();
	}
}