<?php
namespace rocket\spec\ei\manage\util\model;

use rocket\ajah\AjahEventInfo;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\manage\EiEntry;

class EiAjahEventInfo extends AjahEventInfo {
	
	public function eiSpecChanged(...$eiSpecArgs) {
		foreach ($eiSpecArgs as $eiSpecArg) {
			$this->groupChanged(self::buildTypeId(EiuFactory::buildEiSpecFromEiArg($eiSpecArg)));
		}
	}
	
	public function eiEntryChanged(...$eiEntryArgs) {
		foreach ($eiEntryArgs as $eiEntryArg) {
			$this->eiEntryMod($eiEntryArg, false);
		}
	}
	
	public function eiEntryRemoved(...$eiEntryArgs) {
		foreach ($eiEntryArgs as $eiEntryArg) {
			$this->eiEntryMod($eiEntryArg, true);
		}
	}
	
	private function eiEntryMod($eiEntryArg, bool $removed) {
		$eiEntry = EiuFactory::buildEiEntryFromEiArg($eiEntryArg, 'eiEntryArg', null, true);
		if ($removed) {
			$this->itemRemoved(self::buildTypeId($eiEntry->getLiveEntry()->getEiSpec()), self::buildItemId($eiEntry));
		} else {
			$this->itemChanged(self::buildTypeId($eiEntry->getLiveEntry()->getEiSpec()), self::buildItemId($eiEntry));
		}
	}
	
	public static function buildTypeId(EiSpec $eiSpec) {
		return $eiSpec->getSupremeEiSpec()->getId();	
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @return string
	 */
	public static function buildItemId(EiEntry $eiEntry) {
		if ($eiEntry->isDraft()) {
			return 'draft-id-' . $eiEntry->getDraft()->getId();
		}
		
		return 'live-id-rep-' . $eiEntry->getLiveEntry()->getId();
	}
	
	public function toAttrs(): array {
	
	}
}
