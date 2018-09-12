<?php
namespace rocket\user\model\security;

use rocket\ei\EiCommandPath;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\security\EiEntryAccessFactory;
use rocket\ei\manage\security\EiEntryAccess;

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
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiEntryAccessFactory::createEiEntryAccess()
	 */
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
			$privilegeSettings[] = $eiGrantPrivilege->getPrivilegeSetting();
		}
		
		return new RestrictedEiEntryAccess($constraintCache->getPrivilegeDefinition(), $privilegeSettings);
		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiEntryAccessFactory::isExecutableBy()
	 */
	public function isExecutableBy(EiCommandPath $eiCommandPath): bool {
		foreach ($this->constraintCaches as $constraintCache) {
			if ($constraintCache->getEiGrant()->isFull()
					|| $constraintCache->getPrivilegeDefinition()->isEiCommandPathUnprivileged($eiCommandPath)) {
				return true;
			}
			
			foreach ($constraintCache->getEiGrant()->getEiGrantPrivileges() as $eiGrantPrivilege) {
				if ($eiGrantPrivilege->getPrivilegeSetting()->acceptsEiCommandPath($eiCommandPath)) {
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
// 			$privilegeSettings[] = $eiGrantPrivilege->getPrivilegeSetting();
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
// 						if ($eiGrantPrivilege->getPrivilegeSetting()->acceptsEiCommandPath($eiCommandPath)) {
// 							return true;
// 						}
// 					}
// 		}
		
// 		return false;
// 	}
	
// }
