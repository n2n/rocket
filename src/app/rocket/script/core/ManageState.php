<?php
namespace rocket\script\core;

use n2n\http\ControllerContext;
use n2n\model\RequestScoped;
use rocket\script\entity\EntityScript;
use rocket\script\entity\manage\ScriptState;
use rocket\user\model\LoginContext;
use n2n\core\N2nContext;
use rocket\user\bo\User;
use rocket\core\model\Rocket;
use n2n\core\IllegalStateException;
use rocket\script\security\SecurityManager;
use n2n\persistence\orm\EntityManager;

class ManageState implements RequestScoped {
	private $n2nContext;
	private $selectedMenuItem;
	private $user;
	private $securityManager;
	private $scriptStates = array();
	private $entityManager;
	
	private function _init(N2nContext $n2nContext, LoginContext $loginContext, Rocket $rocket) {
		$this->n2nContext = $n2nContext;
	
		if (null !== ($user = $loginContext->getCurrentUser())) {
			$this->setUser($user);
		}
	}
		
	public function getN2nContext() {
		return $this->n2nContext;
	}
	
	public function getUser() {
		return $this->user;
	}
	
	public function setUser(User $user) {
		$this->user = $user;
		$this->securityManager = $user->createSecurityManager($this->n2nContext);
	}
	
	public function setSelectedMenuItem(MenuItem $selectedMenuItem) {
		$this->selectedMenuItem = $selectedMenuItem;
	}
	
	public function getSelectedMenuItem() {
		return $this->selectedMenuItem;
	}
	
	public function getSecurityManager() {
		if ($this->securityManager === null) {
			throw new IllegalStateException('No SecurityManager assigned.');
		}
		
		return $this->securityManager;
	}
	
	public function setSecurityManager(SecurityManager $securityManager) {
		$this->securityManager = $securityManager;
	}
	/**
	 * @throws IllegalStateException
	 * @return EntityManager
	 */
	public function getEntityManager() {
		if ($this->entityManager === null) {
			throw new IllegalStateException('No EntityManager assigned.');
		}
		
		return $this->entityManager;
	}
	
	public function setEntityManager(EntityManager $entityManager) {
		$this->entityManager = $entityManager;
	} 
	
	public function createScriptState(EntityScript $entityScript, ControllerContext $controllerContext) {
		if (sizeof($this->scriptStates)) {
			$parentScriptState = end($this->scriptStates);
			return $this->scriptStates[] = $parentScriptState->createChildScriptState($entityScript, $controllerContext, false);
		}
		
		$scriptState = new ScriptState($entityScript, $this, false);
		$scriptState->setControllerContext($controllerContext);
		
		return $this->scriptStates[] = $scriptState;
	}
		
// 	public function createSingleSelectScriptState(EntityScript $entityScript, ControllerContext $controllerContext, $id, $label = null) {
// 		$scriptState = $this->createScriptState($entityScript, $controllerContext);
// 		$scriptState->addHardCriteriaConstraint(new FilterCriteriaConstraint(new SimpleComparatorConstraint(
// 				$entityScript->getEntityModel()->getIdProperty()->getName(), $id)));
		
// 		$scriptState->setOverviewDisabled(true);
// 		if (isset($label)) {
// 			$scriptState->setDetailBreadcrumbLabel($label);
// 		}
		
// 		return $scriptState;
// 	}
	/**
	 * 
	 * @param EntityScript $entityScript
	 * @throws UnsuitableScriptStateException
	 * @return \rocket\script\entity\manage\ScriptState
	 */
	public function peakScriptState(EntityScript $entityScript = null) {
		if (!sizeof($this->scriptStates)) {
			throw new UnsuitableScriptStateException('No ScriptStates available.');
		}  
		
		end($this->scriptStates);
		$scriptState = current($this->scriptStates);
// 		if (isset($entityScript) && !$scriptState->getContextEntityScript()->equals($entityScript)) {
// 			throw new UnsuitableScriptStateException(
// 					'Latest ScriptState is not assigned to passed EntityScript (id: ' . $entityScript->getId() . ').');
// 		}

		return $scriptState;
	}
	
	public function popScriptStateByEntityScript(EntityScript $entityScript) {
		$this->peakScriptState($entityScript);
		return array_pop($this->scriptStates);
	}
	
	public function getMainId() {
		if (!sizeof($this->scriptStates)) {
			return null;
		}
		
		reset($this->scriptStates);
		return current($this->scriptStates)->getId();
	}
}