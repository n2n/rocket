<?php
namespace rocket\script\security;

use rocket\script\core\Script;
use rocket\script\entity\EntityScript;
use rocket\script\core\MenuItem;

class FullAccessSecurityManager implements SecurityManager {
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\security\SecurityManager::getSecurityConstraintByScript()
	 */
	public function getScriptConstraintByScript(Script $script) {
		return null;		
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\security\SecurityManager::getEntityScriptConstraintByEntityScript()
	 */
	public function getEntityScriptConstraintByEntityScript(EntityScript $script) {
		return null;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\security\SecurityManager::isMenuItemIdAccessable()
	 */
	public function isMenuItemIdAccessable($id) {
		return true;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\security\SecurityManager::isMenuItemAccessable()
	 */
	public function isMenuItemAccessable(MenuItem $menuItem) {
		return true;
	}	
}