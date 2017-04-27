<?php
namespace rocket\spec\ei\component\field\impl\relation\model\relation;

use rocket\spec\ei\manage\EiFrame;
use n2n\reflection\property\AccessProxy;
use rocket\spec\ei\component\modificator\impl\adapter\EiModificatorAdapter;
use rocket\spec\ei\manage\mapping\EiEntry;
use rocket\spec\ei\manage\mapping\WrittenMappingListener;
use rocket\spec\ei\manage\util\model\Eiu;

class PlainMappedRelationEiModificator extends EiModificatorAdapter {
	private $targetEiFrame;
	private $entityObj;
	private $targetAccessProxy;
	private $sourceMany;

	public function __construct(EiFrame $targetEiFrame, $entityObj, AccessProxy $targetAccessProxy, bool $sourceMany) {
		$this->targetEiFrame = $targetEiFrame;
		$this->entityObj = $entityObj;
		$this->targetAccessProxy = $targetAccessProxy;
		$this->sourceMany = $sourceMany;
	}

	public function setupEiEntry(Eiu $eiu) {
		$eiFrame = $eiu->frame()->getEiFrame();
		$eiEntry = $eiu->entry()->getEiEntry();
		
		if ($this->targetEiFrame !== $eiFrame
				|| !$eiEntry->getEiObject()->isNew()) return;

		$that = $this;
		$targetEntityObj = $eiEntry->getEiObject()->getLiveObject();
		$eiEntry->registerListener(new WrittenMappingListener(function () use ($that, $targetEntityObj) {
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