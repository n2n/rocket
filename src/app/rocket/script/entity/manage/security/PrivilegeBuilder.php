<?php

namespace rocket\script\entity\manage\security;

use rocket\script\entity\command\ScriptCommand;
use n2n\util\StringUtils;
use rocket\script\entity\command\PrivilegedScriptCommand;
use rocket\script\entity\command\PrivilegeExtendableScriptCommand;

class PrivilegeBuilder {
	const PART_SEPARATOR = '?';
	
	public static function isPrivilegeCommand(ScriptCommand $scriptCommand) {
		return $scriptCommand instanceof PrivilegedScriptCommand 
				|| $scriptCommand instanceof PrivilegeExtendableScriptCommand;
	}
	
	public static function buildPrivilege(ScriptCommand $scriptCommand, $privilegeExt = null) {
		if (!self::isPrivilegeCommand($scriptCommand)) {
			throw new \InvalidArgumentException('Cannot build privilege for this command. Please contact Andreas von Burg.');
		}
		
		return $scriptCommand->getEntityScript()->getId() . self::PART_SEPARATOR . $scriptCommand->getId()
				. self::PART_SEPARATOR . $privilegeExt;
	}
	
// 	public static function isPrivilegeAccessable($privilege, $scriptId, $commandId, $privilegeExt = null) {
// 		$availablePrivilege = self::buildPrivilege($scriptCommand, $privilegeExt);
// 		if ($privilegeExt !== null) {
// 			return $availablePrivilege == $privilege;
// 		}
// 		return StringUtils::startsWith($availablePrivilege, $privilege);
// 	}
	
	public static function testPrivilege($privilegeNeedle, $testablePrivilege) {
		if ((string) $privilegeNeedle === (string) $testablePrivilege) return true;
		
		return StringUtils::startsWith($privilegeNeedle, $testablePrivilege);
	}
	
	public static function testPrivileges($privilegeNeedle, array $testablePrivileges) {
		foreach ($testablePrivileges as $testablePrivilege) {
			if (self::testPrivilege($privilegeNeedle, $testablePrivilege)) {
				return true;
			}
		}
	
		return false;
	}
}