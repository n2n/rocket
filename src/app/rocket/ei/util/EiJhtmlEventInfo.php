<?php
namespace rocket\ei\util;

use rocket\ei\EiType;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\control\Control;
use n2n\web\ui\SimpleBuildContext;
use rocket\ajah\JhtmlEventInfo;
use rocket\ei\manage\veto\EiLifecycleMonitor;

class EiJhtmlEventInfo extends JhtmlEventInfo {
    const ATTR_CHANGES_KEY = 'eiMods';
	const ATTR_SWAP_CONTROL_HTML_KEY = 'swapControlHtml';
	
	const MOD_TYPE_CHANGED = 'changed';
	const MOD_TYPE_REMOVED = 'removed';
	const MOD_TYPE_ADDED = 'added';
	
	private $noAutoEvents = false;
	private $eventMap = array();
	private $swapControl;
	
	private function evMapEiType(string $eiTypeId) {
	    $this->eventMap[$eiTypeId] = self::MOD_TYPE_CHANGED;
	}
	
	/**
	 * @param string $eiTypeId
	 * @param string $entryId
	 * @param string $modType
	 */
	private function evMapEiObject(string $eiTypeId, string $pid = null, int $draftId = null, string $modType) {
	    if (!isset($this->eventMap[$eiTypeId])) {
	        $this->eventMap[$eiTypeId] = array('pids' => [], 'draftIds' => []);
	    }
	    
	    if ($pid !== null) {
	       $this->eventMap[$eiTypeId]['pids'][$pid] = $modType;
	    }
	    
	    if ($draftId !== null) {
	        $this->eventMap[$eiTypeId]['draftIds'][$draftId] = $modType;
	    }
	}
	
	/**
	 * @param bool $noAutoEvents
	 * @return \rocket\ei\util\EiJhtmlEventInfo
	 */
	public function noAutoEvents(bool $noAutoEvents = true) {
		$this->noAutoEvents = true;
		return $this;
	}
	
	/**
	 * @param mixed ...$eiTypeArgs
	 * @return \rocket\ei\util\EiJhtmlEventInfo
	 */
	public function eiTypeChanged(...$eiTypeArgs) {
		foreach ($eiTypeArgs as $eiTypeArg) {
			$this->groupChanged(self::buildTypeId(EiuAnalyst::buildEiTypeFromEiArg($eiTypeArg)));
		}
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return \rocket\ei\util\EiJhtmlEventInfo
	 */
	public function eiObjectAdded(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, self::MOD_TYPE_ADDED);
		}
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return \rocket\ei\util\EiJhtmlEventInfo
	 */
	public function eiObjectChanged(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, self::MOD_TYPE_CHANGED);
		}
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return \rocket\ei\util\EiJhtmlEventInfo
	 */
	public function eiObjectRemoved(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, self::MOD_TYPE_REMOVED);
		}
		return $this;
	}
	
	private function eiObjectMod($eiObjectArg, string $modType) {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', null, true);
		
		$eiTypeId = self::buildTypeId($eiObject->getEiEntityObj()->getEiType());
		
		$pid = null;
		if ($eiObject->getEiEntityObj()->hasId()) {
		    $pid = $eiObject->getEiEntityObj()->getPid();
		}
		
		$draftId = null;
		if ($eiObject->isDraft()) {
		    $draftId = $eiObject->getDraft()->getId();
		}
		
		$this->evMapEiObject($eiTypeId, $pid, $draftId, $modType);
	}
	
	/**
	 * @param Control $control
	 * @return \rocket\ei\util\EiJhtmlEventInfo
	 */
	public function controlSwaped(Control $control) {
		$this->swapControl = $control;
		return $this;
	}
	
	/**
	 * @param EiType $eiType
	 * @return string
	 */
	public static function buildTypeId(EiType $eiType) {
		return $eiType->getSupremeEiType()->getId();	
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return string
	 */
	public static function buildItemId(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			return 'draft-id-' . $eiObject->getDraft()->getId();
		}
		
		return 'live-ei-id-' . $eiObject->getEiEntityObj()->getId();
	}
	
	public function introduceEiLifecycleMonitor(EiLifecycleMonitor $elm) {
		if ($this->noAutoEvents) {
			return;
		}
		
		$taa = $elm->approve();
		
		if (!$taa->isSuccessful()) {
			$this->message(...$taa->getReasonMessages());
			return;
		}
		
		foreach ($elm->getUpdateActions() as $action) {
			$this->eiObjectChanged($action->getEiObject());
		}
		
		foreach ($elm->getPersistActions() as $action) {
			$this->eiObjectAdded($action->getEiObject());
		}
	
		foreach ($elm->getRemoveActions() as $action) {
			$this->eiObjectRemoved($action->getEiObject());
		}
	}
	
	public function toAttrs(): array {
		$attrs = parent::toAttrs(); 
		
		if ($this->swapControl !== null) {
			$attrs[self::ATTR_SWAP_CONTROL_HTML_KEY] = $this->swapControl->createUiComponent()
					->build(new SimpleBuildContext());	
		}
		
		if (!empty($this->eventMap)) {
			$attrs[self::ATTR_CHANGES_KEY] = $this->eventMap;
		}
		
		return $attrs;
	}
}
