<?php
namespace rocket\spec\ei\manage\util\model;

use rocket\ajah\JhtmlEventInfo;
use rocket\spec\ei\EiType;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\control\Control;
use n2n\web\ui\SimpleBuildContext;

class EiJhtmlEventInfo extends JhtmlEventInfo {
	const ATTR_SWAP_CONTROL_HTML_KEY = 'swapControlHtml';
	private $swapControl;
	
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
		if ($removed) {
			$this->itemRemoved(self::buildTypeId($eiObject->getEiEntityObj()->getEiType()), self::buildItemId($eiObject));
		} else {
			$this->itemChanged(self::buildTypeId($eiObject->getEiEntityObj()->getEiType()), self::buildItemId($eiObject));
		}
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
		if ($this->swapControl === null) {
			return array();
		}
		
		return array(self::ATTR_SWAP_CONTROL_HTML_KEY => $this->swapControl->createUiComponent()
				->build(new SimpleBuildContext()));	
	}
}
