<?php
namespace rocket\script\entity\listener\impl\cache;

use n2n\model\RequestScoped;
use n2n\ui\ViewFactory;
class ClearCacheIndicator implements RequestScoped {
	private $cacheCleared;
	
	public function isCacheCleared() {
		return (bool) $this->cacheCleared;
	}
	
	public function setCacheCleared($cacheCleared) {
		$this->cacheCleared = $cacheCleared;
	}
	
	public function clearCache() {
		if ($this->isCacheCleared()) return;
		ViewFactory::getCacheStore()->clear();
		$this->cacheCleared = true;
	}
}