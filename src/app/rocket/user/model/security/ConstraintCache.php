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
namespace rocket\user\model\security;

use rocket\ei\manage\security\filter\SecurityFilterDefinition;
use rocket\user\bo\EiGrantPrivilege;
use rocket\ei\manage\frame\CriteriaConstraint;
use rocket\ei\manage\entry\EiEntryConstraint;
use n2n\util\type\ArgUtils;
use rocket\user\bo\EiGrant;
use rocket\ei\manage\security\privilege\PrivilegeDefinition;

class ConstraintCache {
	private $eiGrant;
	/**
	 * @var PrivilegeDefinition
	 */
	private $privilegeDefinition;
	/**
	 * @var SecurityFilterDefinition
	 */
	private $securityFilterDefinition;
	
	/**
	 * @var CriteriaConstraint[]
	 */
	private $criteriaConstraints = array();
	/**
	 * @var EiEntryConstraint[]
	 */
	private $eiEntryConstraints = array();
	
	function __construct(EiGrant $eiGrant, PrivilegeDefinition $privilegeDefinition, SecurityFilterDefinition $securityFilterDefinition) {
		$this->eiGrant = $eiGrant;
		$this->privilegeDefinition = $privilegeDefinition;
		$this->securityFilterDefinition = $securityFilterDefinition;
	}
	
	/**
	 * @return EiGrant 
	 */
	function getEiGrant() {
		return $this->eiGrant;
	}
	
	/**
	 * @return \rocket\ei\manage\security\privilege\PrivilegeDefinition
	 */
	function getPrivilegeDefinition() {
		return $this->privilegeDefinition;
	}
	
	/**
	 * @return \rocket\ei\manage\security\filter\SecurityFilterDefinition
	 */
	function getSecurityFilterDefinition() {
		return $this->securityFilterDefinition;
	}

	/**
	 * @param EiGrantPrivilege $eiGrantPrivilege
	 * @return CriteriaConstraint
	 */
	function getCriteriaConstraint(EiGrantPrivilege $eiGrantPrivilege) {
		ArgUtils::assertTrue($eiGrantPrivilege->isRestricted());
		
		$objHash = spl_object_hash($eiGrantPrivilege);
		
		if (isset($this->criteriaConstraints[$objHash])) {
			return $this->criteriaConstraints[$objHash];
		}
		
		return $this->criteriaConstraints[$objHash] = $this->securityFilterDefinition
				->toFilterDefinition()
				->createCriteriaConstraint($eiGrantPrivilege->readRestrictionFilterSettingGroup());
	}
	
	/**
	 * @param EiGrantPrivilege $eiGrantPrivilege
	 * @return \rocket\ei\manage\entry\EiEntryConstraint
	 */
	function getEiEntryConstraint(EiGrantPrivilege $eiGrantPrivilege) {
		ArgUtils::assertTrue($eiGrantPrivilege->isRestricted());
		
		$objHash = spl_object_hash($eiGrantPrivilege);
		
		if (isset($this->eiEntryConstraints[$objHash])) {
			return $this->eiEntryConstraints[$objHash];
		}
		
		return $this->eiEntryConstraints[$objHash] = $this->securityFilterDefinition
				->createEiEntryConstraint($eiGrantPrivilege->readRestrictionFilterSettingGroup());
	}
}