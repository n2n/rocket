<?php
namespace rocket\script\entity\modificator\impl\cache;

use rocket\script\entity\modificator\impl\IndependentScriptModificatorAdapter;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\mapping\OnWriteMappingListener;
use n2n\util\Attributes;
use rocket\script\entity\listener\impl\cache\ClearCacheIndicator;
use n2n\N2N;

class ClearCacheModificator extends IndependentScriptModificatorAdapter {
	
	private $clearChacheIndicator;
	
	private function _init(ClearCacheIndicator $clearChacheIndicator) {
		$this->clearChacheIndicator = $clearChacheIndicator;
	}
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		N2N::getN2nContext()->magicInit($this);
	}
	
	public function setupScriptSelectionMapping(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping) {
		
		$that = $this;
		
		$scriptSelectionMapping->registerListener(new OnWriteMappingListener(function() use ($that) {
			$that->clearChacheIndicator->clearCache();
		}));
	}
	
}