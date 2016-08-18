<?php
namespace rocket\script\security;

use rocket\script\core\Script;
use rocket\script\entity\EntityScript;
use rocket\script\core\MenuItem;
use rocket\script\entity\security\CommonEntityScriptConstraint;
use n2n\core\N2nContext;

class ScriptSecurityManager implements SecurityManager {
	private $n2nContext;
	private $fullAccessScriptIds = array();
	private $scriptGrants = array();
	private $accessableMenuItemIds = array();
	private $scriptConstraints = array();
	private $entityScriptConstraints = array();
	
	public function __construct(N2nContext $n2nContext) {
		$this->n2nContext = $n2nContext;
	}
	
	public function addFullAccessableScriptId($scriptId) {
		$this->fullAccessScriptIds[$scriptId] = $scriptId;
		unset($this->scriptGrants[$scriptId]);
		unset($this->entityScriptConstraints[$scriptId]);
	}	

	public function getFullAccessableScriptIds() {
		return $this->fullAccessScriptIds;
	}
	
	public function removeFullAccesableScriptId($scriptId) {
		unset($this->fullAccessScriptIds[$scriptId]);
	}
	
	public function addScriptGrant($scriptId, ScriptGrant $scriptGrant) {
		if (isset($this->fullAccessScriptIds[$scriptId])) {
			return;
		}
		
		if (!isset($this->scriptGrants[$scriptId])) {
			$this->scriptGrants[$scriptId] = array(); 
		}
		
		$this->scriptGrants[$scriptId][] = $scriptGrant;
	}
	
	public function getScriptConstraintByScript(Script $script) {
		if ($script instanceof EntityScript) {
			return $this->getEntityScriptConstraintByScript($script);
		}
		
		$scriptId = $script->getId();
		if (isset($this->fullAccessScriptIds[$scriptId])) {
			return null;
		}
		
		if (isset($this->scriptConstraints[$scriptId])) {
			return $this->scriptConstraints[$scriptId];
		}
				
		$this->scriptConstraints[$scriptId] = $constraint = new CommonScriptConstraint();
		if (!isset($this->scriptGrants[$scriptId])) return $constraint;
		
		foreach ($this->scriptGrants[$scriptId] as $scriptGrant) {
			$constraint->addScriptGrant($scriptGrant);
		}
		
		return $constraint;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\security\SecurityManager::getEntityScriptConstraintByScript()
	 */
	public function getEntityScriptConstraintByEntityScript(EntityScript $script) {
		$script = $script->getTopEntityScript();
		$scriptId = $script->getId();
		if (isset($this->fullAccessScriptIds[$scriptId])) {
			return null;
		}
		
		if (isset($this->entityScriptConstraints[$scriptId])) {
			return $this->entityScriptConstraints[$scriptId];
		}
		
		$this->entityScriptConstraints[$scriptId] = $constraint = new CommonEntityScriptConstraint($script, $this->n2nContext);
		if (!isset($this->scriptGrants[$scriptId])) return $constraint;
		
		foreach ($this->scriptGrants[$scriptId] as $scriptGrant) {
			$constraint->addScriptGrant($scriptGrant);
		}
		
		return $constraint;
	}
	
	public function addAccessableMenuItemIds(array $menuItemIds) {
		foreach ($menuItemIds as $menuItemId) {
			$this->accessableMenuItemIds[$menuItemId] = $menuItemId;
		}
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\security\SecurityManager::isMenuItemIdAccessable()
	 */
	public function isMenuItemIdAccessable($id) {
		return $this->accessableMenuItemIds[$id];
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\security\SecurityManager::isMenuItemAccessable()
	 */
	public function isMenuItemAccessable(MenuItem $menuItem) {
		return $this->isMenuItemIdAccessable($menuItem->getId());
	}
	

// 	public function isScriptCommandAvailable(ScriptCommand $scriptCommand, $privilegeExt = null) {
// 		$scriptId = $scriptCommand->getEntityScript()->getTopEntityScript()->getId();
		
// 		foreach ($this->scriptGrants as $scriptGrant) {
// 			if ($this->isGrantAccessable($scriptGrant, $scriptId, $scriptCommand)) {
// 				return true;
// 			}
// 		}
		
// 		return false;
// 	}
		
// 	private function isGrantAccessable(ScriptGrant $scriptGrant, $scriptId, ScriptCommand $scriptCommand, $privilegeExt = null) {
// 		if ($scriptGrant->getScriptId() != $scriptId) return false;
			
// 		if ($privilegeExt === null &&
// 				!($scriptCommand instanceof PrivilegedScriptCommand
// 						|| $scriptCommand instanceof PrivilegeExtendableScriptCommand)) {
// 			return true;
// 		}
		
// 		foreach ($scriptGrant->getPrivilegesGrants() as $privilegesGrant) {
// 			if (!$privilegesGrant->isRestricted()) return true;
			
// 			if (self::containsAccessablePrivilege($scriptGrant->getPrivileges(), $scriptCommand, $privilegeExt)) {
// 				return true;
// 			}
// 		}
		
// 		return false;
// 	}
	
// 	private function filterAccessableGrants(ScriptCommand $scriptCommand, $privilegeExt = null) {
// 		$scriptId = $scriptCommand->getEntityScript()->getTopEntityScript()->getId();
		
// 		$accessableScriptGrants = array();
// 		foreach ($this->scriptGrants as $scriptGrant) {
// 			if ($this->isGrantAccessable($scriptGrant, $scriptId, $scriptCommand)) {
// 				$accessableScriptGrants[] = $scriptGrant;
// 			}
// 		}
// 		return $accessableScriptGrants;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \rocket\script\entity\manage\SecurityManager::applyConstraintsToCriteria()
// 	 */
// 	public function applyConstraintsToCriteria(Criteria $criteria, CriteriaProperty $entityAlias, 
// 			ScriptState $scriptState, ScriptCommand $currentCommand, $privilegeExt = null) {
// 		foreach ($this->findCriteriaConstraints($scriptState, $currentCommand, $privilegeExt) as $criteriaConstraint) {
// 			$criteriaConstraint->applyToCriteria($criteria, $entityAlias);
// 		}
// 	}
	
// 	private function findCriteriaConstraints(ScriptState $scriptState, ScriptCommand $scriptCommand, $privilegeExt) {
// 		$accessableGrants = $this->filterAccessableGrants($scriptCommand, $privilegeExt);
			
// 		foreach ($accessableGrants as $accessableGrant) {
// 			if (!$accessableGrant->isRestricted()) return array();
// 		}
		
// 		$accessable = false;
// 		$filterModel = FilterModel::createFromFilterItems($scriptCommand->getEntityScript()
// 				->getTopEntityScript()->createRestrictionSelectorItems($scriptState->getN2nContext()));
		
// 		$criteriaConstraints = array();
// 		foreach ($accessableGrants as $accessableGrant) {				
// 			$criteriaConstraints[] = $filterModel->createCriteriaConstraint($accessableGrant->readRestrictionFilterData());
// 			$accessable = true;
// 		}
	
// 		if (!$accessable) {
// 			throw new IllegalStateException('No access to privilege');
// 		}
	
// 		return $criteriaConstraints;
// 	}
	
// 	public function createPrivilegeConstraint(ScriptState $scriptState, ScriptCommand $scriptCommand, $privilegeExt = null) {
// 		$entityScript = $scriptCommand->getEntityScript()->getTopEntityScript();
// 		$scriptId = $entityScript->getId();
		
// 		$selectorModel = SelectorModel::createFromSelectorItems($entityScript->createRestrictionSelectorItems($scriptState->getN2nContext(), $scriptState));
		
// 		$accessGrants = array();
// 		$privilegeGrants = array();
		
// 		foreach ($this->scriptGrants as $scriptGrant) {
// 			if ($scriptGrant->getScriptId() != $scriptId) continue;
			
// 			$accessAttributes = null;
// 			$restrictionSelector = null;
// 			if ($scriptGrant->isRestricted()) {
// 				$accessAttributes = $scriptGrant->getAccessAttributes();
// 				$restrictionSelector = $selectorModel->createSelector($scriptGrant->getRestrictionFilterData());
// 			}
			
// 			if ($this->isGrantAccessable($scriptGrant, $scriptId, $scriptCommand)) {
// 				$accessGrants[] = new AccessGrant($accessAttributes, $restrictionSelector); 
// 			}
			
// 			$privilegeGrants[] = new PrivilegeGrant($scriptGrant->getPrivileges(), $restrictionSelector);
// 		}
		
// 		return new PrivilegeConstraint($accessGrants, $privilegeGrants);
// 	}

	
	
	
// 	public static function buildScriptFieldAttributesKey(ScriptField $scriptField) {
// 		return $scriptField->getEntityScript()->getId() . self::PART_SEPARATOR . $scriptField->getId();
// 	}
}

