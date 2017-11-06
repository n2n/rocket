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
namespace rocket\spec\ei\security;

use rocket\spec\ei\manage\critmod\SelectorModel;
use n2n\core\container\N2nContext;
use rocket\spec\ei\EiType;
use rocket\spec\ei\component\command\EiCommand;
use rocket\spec\ei\component\command\PrivilegedEiCommand;
use rocket\spec\ei\component\command\PrivilegeExtendableEiCommand;
use rocket\spec\ei\manage\mapping\WhitelistEiCommandAccessRestrictor;
use rocket\spec\ei\manage\security\PrivilegeBuilder;
use rocket\spec\security\ScriptGrant;
use rocket\spec\ei\manage\critmod\filter\FilterDefinition;

class CommonConstraint implements Constraint {
	private $eiType;
	private $n2nContext;
	private $accessAttributes = array();
	private $privilegeGrants = array();
	private $privilegesGrantItems = array();
	private $selectorItems;
	private $filterModel;
	private $selectorModel;
	
	public function __construct(EiType $eiType, N2nContext $n2nContext) {
		$this->eiType = $eiType;
		$this->n2nContext = $n2nContext;
	}
	
	public function getAccessAttributes() {
		return $this->accessAttributes;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\security\ScriptConstraint::getPrivilegesGrants()
	*/
	public function getPrivilegesGrants() {
		return $this->privilegesGrants;
	}
	
	public function addScriptGrant(ScriptGrant $scriptGrant) {
		$this->accessAttributes[] = $scriptGrant->getAccessAttributes();
		foreach ($scriptGrant->getPrivilegesGrants() as $privilegeGrant) {
			$this->privilegesGrantItems[] = new PrivilegesGrantItem($privilegeGrant->getPrivileges(),
					$privilegeGrant->getRestrictionFilterData(), $this);
		}
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\security\SecurityConstraint::isEiCommandAvailable()
	 */
	public function isEiCommandAvailable(EiCommand $eiCommand, $privilegeExt = null) {
		if ($privilegeExt === null && !($eiCommand instanceof PrivilegedEiCommand
				|| $eiCommand instanceof PrivilegeExtendableEiCommand)) {
			return true;
		}
		
		$privilege = PrivilegeBuilder::buildPrivilege($eiCommand, $privilegeExt);
		foreach ($this->privilegesGrantItems as $item) {
			if ($item->isPrivilegeaccessible($privilege)) {
				return true;
			}
		}
		
		return false;
	}
	
	private function getSelectorItems() {
		if ($this->selectorItems === null) {
			$this->selectorItems = $this->eiType->createRestrictionSelectorItems($this->n2nContext);
		}
		
		return $this->selectorItems;
	}
	
	public function getOrCreateFilterModel() {
		if ($this->filterModel === null) {
			$this->filterModel = FilterDefinition::createFromFilterFields($this->getSelectorItems());
		}
		
		return $this->filterModel;
	}
	
	public function getOrCreateSelectorModel() {
		if ($this->selectorModel === null) {
			$this->selectorModel = SelectorModel::createFromSelectorItems($this->getSelectorItems());
		}
		
		return $this->selectorModel;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\security\SecurityConstraint::createEiCommandAccessRestrictor()
	 */
	public function createEiCommandAccessRestrictor(\ArrayAccess $values) {
		$accessRestirctor = new WhitelistEiCommandAccessRestrictor();
		foreach ($this->privilegesGrantItems as $privilegeGrantItem) {
			if (!$privilegeGrantItem->acceptsValues($values)) continue;
			
			foreach ($privilegeGrantItem->getPrivileges() as $privilege) {
				$accessRestirctor->addPrivilege($privilege);
			}
		}
		return $accessRestirctor;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\security\SecurityConstraint::createCommandExecutionConstraint()
	 */
	public function createCommandExecutionConstraint(EiCommand $command, $privilegeExt = null) {
		$privilege = null;
		if ($command instanceof PrivilegedEiCommand
				|| $command instanceof PrivilegeExtendableEiCommand) {
			$privilege = PrivilegeBuilder::buildPrivilege($command, $privilegeExt);
		}
		
		$items = array();
		foreach ($this->privilegesGrantItems as $item) {
			if ($privilege !== null && !$item->isPrivilegeaccessible($privilege)) continue;
			
			if (!$item->isRestricted()) {
				return new EmptyCommandExecutionConstraint();
			}
		
			$items[] = $item;
		}
		
		return new EiCommandExecutionConstraint($items);
	}	
}
