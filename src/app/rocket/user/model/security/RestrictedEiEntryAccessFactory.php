<?php
namespace rocket\user\model\security;

use rocket\ei\EiCommandPath;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\security\EiEntryAccess;

class RestrictedEiEntryAccessFactory {
	/**
	 * @var EiGrantConstraintCache[] $consetraintCaches
	 */
	private $constraintCaches = array();
	
	/**
	 * @param EiGrantConstraintCache $constraintCache
	 */
	function addEiGrantConstraintCache(EiGrantConstraintCache $constraintCache) {
		$this->constraintCaches[(string) $constraintCache->getEiGrant()->getEiTypePath()] = $constraintCache;
	}
	
	function createEiEntryAccess(EiEntry $eiEntry): EiEntryAccess {
		$writableEiPropPaths = [];
		$executableEiCommandPaths = [];
		
		$eiEntryMask = $eiEntry->getEiMask();
		foreach ($eiEntryMask->getEiType()->getAllSuperEiTypes(true) as $eiType) {
			$eiTypePathStr = (string) $eiEntryMask->determineEiMask($eiType)->getEiTypePath();
			
			if (!isset($this->constraintCaches[$eiTypePathStr])) {
				continue;
			}
			
			$result = $this->constraintCaches[$eiTypePathStr]->testEiEntryAccess();
			array_push($writableEiPropPaths, ...$result->getWritableEiPropPaths());
			array_push($executableEiCommandPaths, ...$result->getExecutableEiCommandPaths());
		}
		
		return new RestrictedEiEntryAccess($writableEiPropPaths, $executableEiCommandPaths);	
	}
}