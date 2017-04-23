<?php
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\EiObject;
use n2n\util\StringUtils;

class GeneralIdUtils {
	const LIVE_ID_REP_PREFIX = 'live-id-rep-';
	const DRAFT_ID_PREFIX = 'draft-id-';
	
	public static function gernealIdToLiveIdRep(string $generalId) {
		if (!StringUtils::startsWith(self::LIVE_ID_REP_PREFIX, $generalId)) return null;
			
		return mb_substr($generalId, mb_strlen(self::LIVE_ID_REP_PREFIX));
	}
	
	public static function generalIdToDraftId(string $generalId) {
		if (!StringUtils::startsWith(self::DRAFT_ID_PREFIX, $generalId)) return null;
			
		return mb_substr($generalId, mb_strlen(self::DRAFT_ID_PREFIX));
	}
	
	public static function liveIdRepToGeneralId(string $liveIdRep) {
		return self::LIVE_ID_REP_PREFIX . $liveIdRep;
	}
	
	public static function draftIdToGeneralId(int $draftId) {
		return self::DRAFT_ID_PREFIX . $draftId;
	}
	
	public static function generalIdOf(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			$draft = $eiObject->getDraft();
			
			if ($draft->isNew()) return null;
			
			return self::draftIdRepToGeneralId($draft->getId());	
		}
		
		$eiEntityObj = $eiObject->getEiEntityObj();
		
		if (!$eiObject->getEiEntityObj()->isPersistent()) return null;
			
		return self::liveIdRepToGeneralId($eiEntityObj->getEiType()->idToIdRep($eiEntityObj->getId()));
	}
	
	
	
}

