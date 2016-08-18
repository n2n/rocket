<?php
namespace rocket\script\entity\listener;

use n2n\persistence\orm\Entity;
use rocket\script\entity\EntityScript;
use n2n\persistence\orm\store\EntityFlushEvent;
use n2n\persistence\orm\EntityManager;

class EntityChangeEvent {
	const TYPE_ON_INSERT = EntityFlushEvent::TYPE_ON_INSERT;
	const TYPE_INSERTED = EntityFlushEvent::TYPE_INSERTED;
	const TYPE_ON_UPDATE = EntityFlushEvent::TYPE_ON_UPDATE;
	const TYPE_UPDATED = EntityFlushEvent::TYPE_UPDATED;
	const TYPE_ON_DELETE = EntityFlushEvent::TYPE_ON_REMOVE;
	const TYPE_DELETED = EntityFlushEvent::TYPE_REMOVED;
	/**
	 * @var string
	 */
	protected $type;
	/**
	 * @var mixed
	 */
	protected $id;
	/**
	 * @var \n2n\persistence\orm\Entity
	 */
	protected $entity;
	/**
	 * @var \rocket\script\entity\EntityScript
	 */
	protected $entityScript;
	/**
	 * @var \n2n\persistence\orm\EntityManager
	 */
	protected $em;
	/**
	 * @param string $type
	 * @param string $id
	 * @param Entity $entity
	 * @param EntityScript $entityScript
	 */
	public function __construct($type, $id, Entity $entity, EntityScript $entityScript, EntityManager $em) {
		$this->type = $type;
		$this->id = $id;
		$this->entity = $entity;
		$this->entityScript = $entityScript;
		$this->em = $em;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getEntity() {
		return $this->entity;
	}
	
	public function getEntityScript() {
		return $this->entityScript;
	}
	
	public function getEntityManager() {
		return $this->em;
	}
}