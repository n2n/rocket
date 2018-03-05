<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ei\manage\security;

use rocket\ei\component\command\EiCommand;
use n2n\util\StringUtils;
use rocket\ei\component\command\PrivilegedEiCommand;

class PrivilegeBuilder {
	const PART_SEPARATOR = '?';
	
	public static function isPrivilegeCommand(EiCommand $eiCommand) {
		return $eiCommand instanceof PrivilegedEiCommand 
				/*|| $eiCommand instanceof PrivilegeExtendableEiCommand*/;
	}
	
	public static function buildPrivilege(EiCommand $eiCommand, $privilegeExt = null) {
		if (!self::isPrivilegeCommand($eiCommand)) {
			throw new \InvalidArgumentException('Cannot build privilege for this command. Please contact Andreas von Burg.');
		}
		
		return $eiCommand->getEiType()->getId() . self::PART_SEPARATOR . $eiCommand->getId()
				. self::PART_SEPARATOR . $privilegeExt;
	}
	
// 	public static function isPrivilegeaccessible($privilege, $scriptId, $commandId, $privilegeExt = null) {
// 		$availablePrivilege = self::buildPrivilege($eiCommand, $privilegeExt);
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
