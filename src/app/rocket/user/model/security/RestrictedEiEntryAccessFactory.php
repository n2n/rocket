<?php
namespace rocket\ei\manage\security;

use rocket\user\model\security\ConstraintCache;
use rocket\user\model\security\StaticEiEntryAccess;
use rocket\user\model\security\RestrictedEiEntryAccess;
use rocket\ei\EiCommandPath;
use rocket\ei\manage\mapping\EiEntry;

class RestrictedEiEntryAccessFactory implements EiEntryAccessFactory {
	/**
	 * @var ConstraintCache[] $consetraintCaches
	 */
	private $constraintCaches = array();
	
	function __construct(ConstraintCache $constraintCache) {
		$this->addSubEiGrant($constraintCache);
	}
	
	/**
	 * @param ConstraintCache $constraintCache
	 */
	function addSubEiGrant(ConstraintCache $constraintCache) {
		$this->constraintCaches[(string) $constraintCache->getEiGrant()->getEiTypePath()] = $constraintCache;
	}
	
	function createEiEntryAccess(EiEntry $eiEntry): EiEntryAccess {
		$eiTypePathStr = (string) $eiEntry->getEiMask()->getEiTypePath();
		
		if (!isset($this->constraintCaches[$eiTypePathStr])) {
			return new StaticEiEntryAccess(false);
		}
		
		$constraintCache = $this->constraintCaches[$eiTypePathStr];
		if ($constraintCache->getEiGrant()->isFull()) {
			return new StaticEiEntryAccess(true);
		}
		
		$privilegeSettings = array();
		foreach ($constraintCache->getEiGrant()->getEiGrantPrivileges() as $eiGrantPrivilege) {
			$privilegeSettings[] = $constraintCache->getPrivilegeSetting($eiGrantPrivilege);
		}
		
		return new RestrictedEiEntryAccess($constraintCache->getPrivilegeDefinition(), $privilegeSettings);
		
	}
	
	public function isExecutableBy(EiCommandPath $eiCommandPath): bool {
		foreach ($this->constraintCaches as $constraintCache) {
			if ($constraintCache->getEiGrant()->isFull()
					|| $constraintCache->getPrivilegeDefinition()->isEiCommandPathUnprivileged($eiCommandPath)) {
				return true;
			}
			
			foreach ($constraintCache->getEiGrant()->getEiGrantPrivileges() as $eiGrantPrivilege) {
				if ($constraintCache->getPrivilegeSetting($eiGrantPrivilege)->acceptsEiCommandPath($eiCommandPath)) {
					return true;
				}
			}
		}
		
		return false;
	}
	
}



// class RestrictedEiEntryAccessFactory implements EiEntryAccessFactory {
// 	/**
// 	 * @var ConstraintCache[] $consetraintCaches
// 	 */
// 	private $constraintCaches = array();
	
// 	function __construct(ConstraintCache $constraintCache) {
// 		$this->addSubEiGrant($constraintCache);
// 	}
	
// 	/**
// 	 * @param ConstraintCache $constraintCache
// 	 */
// 	function addSubEiGrant(ConstraintCache $constraintCache) {
// 		$this->constraintCaches[(string) $constraintCache->getEiGrant()->getEiTypePath()] = $constraintCache;
// 	}
	
// 	function createEiEntryAccess(EiEntry $eiEntry): EiEntryAccess {
// 		$eiTypePathStr = (string) $eiEntry->getEiMask()->getEiTypePath();
		
// 		if (!isset($this->constraintCaches[$eiTypePathStr])) {
// 			return new StaticEiEntryAccess(false);
// 		}
		
// 		$constraintCache = $this->constraintCaches[$eiTypePathStr];
// 		if ($constraintCache->getEiGrant()->isFull()) {
// 			return new StaticEiEntryAccess(true);
// 		}
		
// 		$privilegeSettings = array();
// 		foreach ($constraintCache->getEiGrant()->getEiGrantPrivileges() as $eiGrantPrivilege) {
// 			$privilegeSettings[] = $constraintCache->getPrivilegeSetting($eiGrantPrivilege);
// 		}
		
// 		return new RestrictedEiEntryAccess($constraintCache->getPrivilegeDefinition(), $privilegeSettings);
		
// 	}
	
// 	public function isExecutableBy(EiCommandPath $eiCommandPath): bool {
// 		foreach ($this->constraintCaches as $constraintCache) {
// 			if ($constraintCache->getEiGrant()->isFull()
// 					|| $constraintCache->getPrivilegeDefinition()->isEiCommandPathUnprivileged($eiCommandPath)) {
// 						return true;
// 					}
					
// 					foreach ($constraintCache->getEiGrant()->getEiGrantPrivileges() as $eiGrantPrivilege) {
// 						if ($constraintCache->getPrivilegeSetting($eiGrantPrivilege)->acceptsEiCommandPath($eiCommandPath)) {
// 							return true;
// 						}
// 					}
// 		}
		
// 		return false;
// 	}
	
// }
