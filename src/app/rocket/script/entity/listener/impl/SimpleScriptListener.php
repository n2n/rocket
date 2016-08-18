<?php
namespace rocket\script\entity\listener\impl;

use n2n\persistence\orm\Entity;

class SimpleScriptListener extends ScriptListenerAdapter {
	private $objectHash;
	private $type;
	private $onChange;
	
	public function __construct(Entity $entity, $type, \Closure $onChange) {
		$this->objectHash = spl_object_hash($entity);
		$this->type = $type;
		$this->onChange = $onChange;	
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\listener\ScriptListener::onEntityChanged()
	 */
	public function onEntityChanged(\rocket\script\entity\listener\EntityChangeEvent $event) {
		if ($this->type & $event->getType() && $this->objectHash == spl_object_hash($event->getEntity())) {
			$this->objectHash->__invoke($event);
		}
	}	
}