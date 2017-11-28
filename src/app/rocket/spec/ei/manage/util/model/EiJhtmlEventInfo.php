<?php
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\EiType;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\control\Control;
use n2n\web\ui\SimpleBuildContext;

class EiJhtmlEventInfo {
    const ATTR_CHANGES_KEY = 'eiMods';
	const ATTR_SWAP_CONTROL_HTML_KEY = 'swapControlHtml';
	
	const MOD_TYPE_CHANGED = 'changed';
	const MOD_TYPE_REMOVED = 'removed';
	
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
	private function evMapEiObject(string $eiTypeId, string $idRep = null, int $draftId = null, string $modType) {
	    if (!isset($this->eventMap[$eiTypeId])) {
	        $this->eventMap[$eiTypeId] = array('idReps' => [], 'draftIds' => []);
	    } else if ($this->eventMap[$eiTypeId] == self::MOD_TYPE_CHANGED) {
	        return;
	    }
	    
	    if ($idRep !== null) {
	       $this->eventMap[$eiTypeId]['idReps'][$idRep] = $modType;
	    }
	    
	    if ($draftId !== null) {
	        $this->eventMap[$eiTypeId]['draftIds'][$draftId] = $modType;
	    }
	}
	
	/**
	 * @param mixed ...$eiTypeArgs
	 * @return \rocket\spec\ei\manage\util\model\EiJhtmlEventInfo
	 */
	public function eiTypeChanged(...$eiTypeArgs) {
		foreach ($eiTypeArgs as $eiTypeArg) {
			$this->groupChanged(self::buildTypeId(EiuFactory::buildEiTypeFromEiArg($eiTypeArg)));
		}
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return \rocket\spec\ei\manage\util\model\EiJhtmlEventInfo
	 */
	public function eiObjectChanged(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, false);
		}
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return \rocket\spec\ei\manage\util\model\EiJhtmlEventInfo
	 */
	public function eiObjectRemoved(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, true);
		}
		return $this;
	}
	
	private function eiObjectMod($eiObjectArg, bool $removed) {
		$eiObject = EiuFactory::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', null, true);
		
		$eiTypeId = self::buildTypeId($eiObject->getEiEntityObj()->getEiType());
		$modType = $removed ? self::MOD_TYPE_REMOVED : self::MOD_TYPE_CHANGED;
		
		$idRep = null;
		if (!$eiObject->isNew()) {
		    $idRep = $eiObject->getEiEntityObj()->getIdRep();
		}
		
		$draftId = null;
		if ($eiObject->isDraft()) {
		    $draftId = $eiObject->getDraft()->getId();
		}
		
		$this->evMapEiObject($eiTypeId, $idRep, $draftId, $modType);
	}
	
	/**
	 * @param Control $control
	 * @return \rocket\spec\ei\manage\util\model\EiJhtmlEventInfo
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
		
		return 'live-id-rep-' . $eiObject->getEiEntityObj()->getId();
	}
	
	public function toAttrs(): array {
		$attrs = array(); 
		
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
