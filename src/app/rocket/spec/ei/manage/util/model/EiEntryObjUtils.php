<?php

namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\LiveEntry;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\LiveEiSelection;
use rocket\spec\ei\manage\DraftEiSelection;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\draft\Draft;

class EiEntryObjUtils {
	
	/**
	 * @param unknown $eiEntryObj
	 * @return rocket\spec\ei\manage\util\model\EiSelection
	 */
	public static function determineEiSelection($eiEntryObj) {
		if ($eiEntryObj instanceof EiSelection) {
			return $eiEntryObj;
		} else if ($eiEntryObj instanceof EiMapping) {
			return $eiEntryObj->getEiSelection();
		} else if ($eiEntryObj instanceof LiveEntry) {
			return new LiveEiSelection($eiEntryObj);
		} else if ($eiEntryObj instanceof Draft) {
			return new DraftEiSelection($eiEntryObj);
		} else {
			ArgUtils::valType($eiEntryObj, array(EiSelection::class, EiMapping::class, LiveEntry::class, Draft::class), 
					false, 'eiEntryObj');
		}
	}
}

