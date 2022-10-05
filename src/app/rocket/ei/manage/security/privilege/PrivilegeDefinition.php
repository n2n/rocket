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
namespace rocket\ei\manage\security\privilege;

use rocket\ei\EiCommandPath;
use rocket\ei\EiPropPath;
use rocket\ei\component\command\EiCmdNature;
use rocket\ei\component\prop\EiPropNature;

class PrivilegeDefinition {
	private $privilegedEiCommands = [];
	private $unprivilegedEiCommands = [];
	private $privilegedEiProps = [];
	private $unprivilegedEiProps = [];
	
	
// 	public function isEmpty(): bool {
// 		return empty($this->eiCommandPrivileges);
// 	}
	
// 	/**
// 	 * @param EiCommandPath $eiCommandPath
// 	 * @return boolean
// 	 * @todo add non privileged cmd paths
// 	 */
// 	function isEiCommandPathUnprivileged(EiCommandPath $eiCommandPath) {
// 		return !$this->checkEiCommandPathForPrivileges($eiCommandPath);
// 	}
	
// 	public function checkEiCommandPathForPrivileges(EiCommandPath $eiCommandPath) {
// 		foreach ($this->privilegedEiCommand as $privilegeEiCommandPathStr => $eiCommandPrivilege) {
// 			$privilegeEiCommandPath = EiCommandPath::create($privilegeEiCommandPathStr);
			
// 			if ($privilegeEiCommandPath->startsWith($eiCommandPath) 
// 					|| $eiCommandPath->startsWith($privilegeEiCommandPath)) {
// 				return true;
// 			}
// 		}
		
// 		return false;
// 	}

	/**
	 * @param EiCmdNature $eiCommand
	 * @return bool
	 */
	function containsEiCommand(EiCmdNature $eiCommand) {
		return $this->containsEiCommandPath(EiCommandPath::from($eiCommand));
	}
		
	/**
	 * @param EiCommandPath $eiCommandPath
	 * @return bool
	 */
	function containsEiCommandPath(EiCommandPath $eiCommandPath) {
		return isset($this->privilegedEiCommands[(string) $eiCommandPath]);
	}
	
	/**
	 * @param EiCmdNature $privilegeEiCommand
	 */
	function addPrivilegedEiCommand(EiCmdNature $privilegeEiCommand) {
		$this->privilegedEiCommands[(string) EiCommandPath::from($privilegeEiCommand)] = $privilegeEiCommand;
	}
	
	/**
	 * @return EiCmdNature[]
	 */
	function getPrivilegedEiCommands() {
		return $this->privilegedEiCommands;
	}
	
	/**
	 * @return EiCmdNature[]
	 */
	function getUnprivilegedEiCommands() {
		return $this->unprivilegedEiCommands;
	}
	
	/**
	 * @param EiCmdNature $unprivilegedEiCommand
	 */
	function addUnprivilegedEiCommand(EiCmdNature $unprivilegedEiCommand) {
		$this->unprivilegedEiCommands[(string) EiCommandPath::from($unprivilegedEiCommand)] = $unprivilegedEiCommand;
	}

	/**
	 * @return EiPropNature[]
	 */
	function getPrivilegedEiProps() {
		return $this->privilegedEiProps;
	}
	
	/**
	 * @param EiPropNature $eiProp
	 */
	function addPrivilegedEiProp(EiPropNature $eiProp) {
		$this->privilegedEiProps[(string) EiPropPath::from($eiProp)] = $eiProp;
	}
	
	/**
	 * @return EiPropNature[]
	 */
	function getUnprivilegedEiProps() {
		return $this->unprivilegedEiProps;
	}
	
	/**
	 * @param EiPropNature $eiProp
	 */
	function addUnprivilegedEiProp(EiPropNature $eiProp) {
		$this->unprivilegedEiProps[(string) EiPropPath::from($eiProp)] = $eiProp;
	}
}
