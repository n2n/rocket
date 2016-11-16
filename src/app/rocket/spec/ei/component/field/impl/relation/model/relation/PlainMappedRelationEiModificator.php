<?php
namespace rocket\spec\ei\component\field\impl\relation\model\relation;

use rocket\spec\ei\manage\EiState;
use n2n\reflection\property\AccessProxy;
use rocket\spec\ei\component\modificator\impl\EiModificatorAdapter;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\mapping\WrittenMappingListener;

class PlainMappedRelationEiModificator extends EiModificatorAdapter {
	private $targetEiState;
	private $entityObj;
	private $targetAccessProxy;
	private $sourceMany;

	public function __construct(EiState $targetEiState, $entityObj, AccessProxy $targetAccessProxy, bool $sourceMany) {
		$this->targetEiState = $targetEiState;
		$this->entityObj = $entityObj;
		$this->targetAccessProxy = $targetAccessProxy;
		$this->sourceMany = $sourceMany;
	}

	public function setupEiMapping(EiState $eiState, EiMapping $eiMapping) {
		if ($this->targetEiState !== $eiState
				|| !$eiMapping->getEiSelection()->isNew()) return;

		$that = $this;
		$targetEntityObj = $eiMapping->getEiSelection()->getLiveObject();
		$eiMapping->registerListener(new WrittenMappingListener(function () use ($that, $targetEntityObj) {
			$this->write($targetEntityObj);
		}));
	}
	
	protected function write($targetEntityObj) {
		if (!$this->sourceMany) {
			$this->targetAccessProxy->setValue($targetEntityObj, $this->entityObj);
			return;
		}
		
		$value = $this->targetAccessProxy->getValue($this->entityObj);
		if ($value === null) {
			$value = new \ArrayObject();
		}
		$value[] = $this->entityObj;
		$this->targetAccessProxy->setValue($targetEntityObj, $value);
	}
}