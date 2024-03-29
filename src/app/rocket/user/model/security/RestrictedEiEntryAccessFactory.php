<?php
namespace rocket\user\model\security;

use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\security\EiEntryAccess;
use rocket\op\ei\manage\entry\EiEntryConstraint;

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
	
	function createEiEntryAccess(EiEntryConstraint $eiEntryConstraint, EiEntry $eiEntry): EiEntryAccess {
		$writableEiPropPaths = [];
		$executableEiCmdPaths = [];
		
		$eiEntryMask = $eiEntry->getEiMask();
		foreach ($eiEntryMask->getEiType()->getAllSuperEiTypes(true) as $eiType) {
			$eiTypePathStr = (string) $eiEntryMask->determineEiMask($eiType)->getEiTypePath();
			
			if (!isset($this->constraintCaches[$eiTypePathStr])) {
				continue;
			}
			
			$result = $this->constraintCaches[$eiTypePathStr]->testEiEntryAccess();
			array_push($writableEiPropPaths, ...$result->getWritableEiPropPaths());
			array_push($executableEiCmdPaths, ...$result->getExecutableEiCmdPaths());
		}
		
		return new RestrictedEiEntryAccess($eiEntryConstraint, $writableEiPropPaths, $executableEiCmdPaths);	
	}
}