<?php
namespace rocket\spec\ei\manage\util\model;

use rocket\ajah\AjahEventInfo;
use rocket\spec\ei\EiType;
use rocket\spec\ei\manage\EiObject;

class EiAjahEventInfo extends AjahEventInfo {
	
	public function eiTypeChanged(...$eiTypeArgs) {
		foreach ($eiTypeArgs as $eiTypeArg) {
			$this->groupChanged(self::buildTypeId(EiuFactory::buildEiTypeFromEiArg($eiTypeArg)));
		}
	}
	
	public function eiObjectChanged(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, false);
		}
	}
	
	public function eiObjectRemoved(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, true);
		}
	}
	
	private function eiObjectMod($eiObjectArg, bool $removed) {
		$eiObject = EiuFactory::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', null, true);
		if ($removed) {
			$this->itemRemoved(self::buildTypeId($eiObject->getEiEntityObj()->getEiType()), self::buildItemId($eiObject));
		} else {
			$this->itemChanged(self::buildTypeId($eiObject->getEiEntityObj()->getEiType()), self::buildItemId($eiObject));
		}
	}
	
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
	
	}
}
