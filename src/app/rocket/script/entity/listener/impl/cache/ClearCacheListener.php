<?php
namespace rocket\script\entity\listener\impl\cache;

use rocket\script\entity\listener\impl\IndependentScriptListenerAdapter;
use rocket\script\entity\listener\EntityChangeEvent;
use n2n\util\Attributes;
use n2n\N2N;

class ClearCacheListener extends IndependentScriptListenerAdapter {
	
	private $clearChacheIndicator;
	
	private function _init(ClearCacheIndicator $clearChacheIndicator) {
		$this->clearChacheIndicator = $clearChacheIndicator;
	}
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		N2N::getN2nContext()->magicInit($this);
	}
	
	public function onEntityChanged(EntityChangeEvent $event) {
		if ($this->clearChacheIndicator->isCacheCleared()) return;
		switch ($event->getType()) {
			case EntityChangeEvent::TYPE_DELETED:
			case EntityChangeEvent::TYPE_INSERTED:
			case EntityChangeEvent::TYPE_UPDATED:
				$this->clearChacheIndicator->clearCache();
		}
	}
}