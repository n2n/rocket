<?php
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\EiObject;
use n2n\util\StringUtils;

class GeneralIdUtils {
	const LIVE_ID_REP_PREFIX = 'live-ei-id-';
	const DRAFT_ID_PREFIX = 'draft-id-';
	
	public static function gernealIdToLiveEiId(string $generalId) {
		if (!StringUtils::startsWith(self::LIVE_ID_REP_PREFIX, $generalId)) return null;
			
		return mb_substr($generalId, mb_strlen(self::LIVE_ID_REP_PREFIX));
	}
	
	public static function generalIdToDraftId(string $generalId) {
		if (!StringUtils::startsWith(self::DRAFT_ID_PREFIX, $generalId)) return null;
			
		return mb_substr($generalId, mb_strlen(self::DRAFT_ID_PREFIX));
	}
	
	public static function liveEiIdToGeneralId(string $liveEiId) {
		return self::LIVE_ID_REP_PREFIX . $liveEiId;
	}
	
	public static function draftIdToGeneralId(int $draftId) {
		return self::DRAFT_ID_PREFIX . $draftId;
	}
	
	public static function generalIdOf(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			$draft = $eiObject->getDraft();
			
			if ($draft->isNew()) return null;
			
			return self::draftEiIdToGeneralId($draft->getId());	
		}
		
		$eiEntityObj = $eiObject->getEiEntityObj();
		
		if (!$eiObject->getEiEntityObj()->isPersistent()) return null;
			
		return self::liveEiIdToGeneralId($eiEntityObj->getEiType()->idToEiId($eiEntityObj->getId()));
	}
	
	
	
}

