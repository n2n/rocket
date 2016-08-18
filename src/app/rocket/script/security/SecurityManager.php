<?php

namespace rocket\script\security;

use rocket\script\entity\EntityScript;
use rocket\script\core\Script;
use rocket\script\core\MenuItem;

interface SecurityManager {
	/**
	 * @param Script $script
	 * @return ScriptConstraint null if SecurityManager imposes no restrictions on passed Script
	 */
	public function getScriptConstraintByScript(Script $script);
	/**
	 * @param EntityScript $script
	 * @return EntityScriptConstraint null if SecurityManager imposes no restrictions on passed EntityScript
	 */
	public function getEntityScriptConstraintByEntityScript(EntityScript $script);
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function isMenuItemIdAccessable($id);
	/**
	 * @param MenuItem $menuItem
	 * @return boolean
	 */
	public function isMenuItemAccessable(MenuItem $menuItem);
}