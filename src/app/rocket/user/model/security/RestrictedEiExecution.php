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

use rocket\ei\security\EiExecution;
use rocket\ei\component\command\EiCommand;
use rocket\ei\manage\security\filter\SecurityFilterDefinition;
use rocket\ei\EiCommandPath;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\critmod\filter\EiEntryConstraintGroup;
use rocket\ei\EiPropPath;
use rocket\ei\manage\security\privilege\PrivilegeDefinition;
use rocket\ei\security\EiPropAccess;
use rocket\ei\security\InaccessibleControlException;
use rocket\ei\manage\critmod\filter\ComparatorConstraintGroup;
use rocket\ei\manage\mapping\EiEntry;
use rocket\user\bo\EiGrantPrivilege;
use rocket\ei\manage\mapping\WhitelistEiCommandAccessRestrictor;

class RestrictedEiExecution implements EiExecution {
	private $eiCommand;
	private $eiGrantPrivileges;
	private $privilegeDefinition;
	private $securityFilterDefinition;
	
	private $eiCommandPath;
	private $eiEntryConstraintGroup;
	private $comparatorConstraintGroup;

	public function __construct(EiCommand $eiCommand = null, EiCommandPath $eiCommandPath, array $eiGrantPrivileges, 
			PrivilegeDefinition $privilegeDefinition, SecurityFilterDefinition $securityFilterDefinition) {
		$this->eiCommand = $eiCommand;
		$this->eiGrantPrivileges = $eiGrantPrivileges;
		$this->privilegeDefinition = $privilegeDefinition;
		$this->securityFilterDefinition = $securityFilterDefinition;
		$this->init($eiCommandPath);
	}

	public function isGranted(): bool {
		return true;
	}

	public function hasEiCommand(): bool {
		return $this->eiCommand !== null;
	}

	public function getEiCommand(): EiCommand {
		if ($this->eiCommand === null) {
			throw new IllegalStateException('No EiCommand executed.');
		}

		return $this->eiCommand;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\security\EiExecution::getEiCommandPath()
	 */
	public function getEiCommandPath(): EiCommandPath {
		return $this->eiCommandPath;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\security\EiExecution::getEiEntryConstraint()
	 */
	public function getEiEntryConstraint() {
		return $this->eiEntryConstraintGroup;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\security\EiExecution::getCriteriaConstraint()
	 */
	public function getCriteriaConstraint() {
		return $this->comparatorConstraintGroup;
	}

	
	public function createEiPropAccess(EiPropPath $eiPropPath): EiPropAccess {
		$attributes = array();
		foreach ($this->eiGrantPrivileges as $eiGrantPrivilege) {
			$eiPropAttributes = PrivilegeDefinition::extractAttributesOfEiPropPrivilege($eiPropPath, 
					$eiGrantPrivilege->readEiPropPrivilegeAttributes());
			if ($eiPropAttributes !== null) {
				$attributes[] = $eiPropAttributes;
			}
		}
		return new RestrictedEiPropAccess($attributes);
	}

	private function init(EiCommandPath $eiCommandPath) {
		if (!$this->privilegeDefinition->checkEiCommandPathForPrivileges($eiCommandPath)) {
			if (empty($this->eiGrantPrivileges)) {
				throw new InaccessibleControlException('EiCommandPath not accessible for current user: ' . $eiCommandPath);
			}	
			
			$this->eiCommandPath = $eiCommandPath;
			$this->initCriteriaConstraint();
			$this->initEiEntryConstraint();
			return;
		}
		
		$newEiGrantPrivileges = array();
		foreach ($this->eiGrantPrivileges as $eiGrantPrivilege) {
			if ($eiGrantPrivilege->acceptsEiCommandPath($eiCommandPath)) {
				$newEiGrantPrivileges[] = $eiGrantPrivilege;
			}
		}
		
		if (empty($newEiGrantPrivileges)) {
			throw new InaccessibleControlException('Privileged EiCommandPath not accessible for current user: ' . $eiCommandPath);
		}
		
		$this->eiGrantPrivileges = $newEiGrantPrivileges;
		$this->eiCommandPath = $eiCommandPath;
		$this->initCriteriaConstraint();
		$this->initEiEntryConstraint();
	}
	
	private function initCriteriaConstraint() {
		$this->comparatorConstraintGroup = new ComparatorConstraintGroup(false);
			
		foreach ($this->eiGrantPrivileges as $eiGrantPrivilege) {
			if (!$eiGrantPrivilege->isRestricted()) {
				$this->comparatorConstraintGroup = null;
				return;
			}
				
			$this->comparatorConstraintGroup->addComparatorConstraint($this->securityFilterDefinition
					->createComparatorConstraint($eiGrantPrivilege->readRestrictionFilterPropSettingGroup()));
		}
	}
	
	private function initEiEntryConstraint() {
		$this->eiEntryConstraintGroup = new EiEntryConstraintGroup(false);
		$this->cachedEiEntryConstraints = array();
			
		foreach ($this->eiGrantPrivileges as $eiGrantPrivilege) {
			$eiEntryConstraint = $this->getOrBuildEiEntryConstraint($eiGrantPrivilege);
			
			if ($eiEntryConstraint === null) {
				$this->eiEntryConstraintGroup = null;
				return;
			}
		
			$this->eiEntryConstraintGroup->add($eiEntryConstraint);
		}
	}
	
	private $cachedEiEntryConstraints = array();
	
	/**
	 * @param EiGrantPrivilege $eiGrantPrivilege
	 * @return \rocket\ei\manage\mapping\EiEntryConstraint
	 */
	private function getOrBuildEiEntryConstraint(EiGrantPrivilege $eiGrantPrivilege) {
		if (!$eiGrantPrivilege->isRestricted()) return null;
		
		$objHash = spl_object_hash($eiGrantPrivilege);
		
		if (isset($this->cachedEiEntryConstraints[$objHash])) {
			return $this->cachedEiEntryConstraints[$objHash];
		}
		
		return $this->cachedEiEntryConstraints[$objHash] = $this->securityFilterDefinition
					->createEiEntryConstraint($eiGrantPrivilege->readRestrictionFilterPropSettingGroup());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\security\EiExecution::extEiCommandPath($ext)
	 */
	public function extEiCommandPath(string $ext) {
		$this->init($this->eiCommandPath->ext($ext));
	}
	
	public function buildEiCommandAccessRestrictor(EiEntry $eiEntry) {
		$restrictor = new WhitelistEiCommandAccessRestrictor();
		
		foreach ($this->eiGrantPrivileges as $eiGrantPrivilege) {
			$eiEntryConstraint = $this->getOrBuildEiEntryConstraint($eiGrantPrivilege);
			
			if ($eiEntryConstraint !== null && !$eiEntryConstraint->check($eiEntry)) {
				continue;
			}
			
			$restrictor->getEiCommandPaths()->addAll($eiGrantPrivilege->getEiCommandPaths());
		}
		
		if ($restrictor->getEiCommandPaths()->isEmpty()) return null;
		
		return $restrictor;
	}
}
