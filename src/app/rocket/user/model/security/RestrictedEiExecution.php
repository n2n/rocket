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

use rocket\ei\manage\security\EiExecution;
use rocket\ei\component\command\EiCommand;
use rocket\ei\manage\security\filter\SecurityFilterDefinition;
use rocket\ei\EiCommandPath;
use rocket\ei\EiPropPath;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\security\privilege\PrivilegeDefinition;
use rocket\ei\manage\critmod\filter\ComparatorConstraintGroup;
use rocket\user\bo\EiGrantPrivilege;
use rocket\ei\manage\security\InaccessibleEiCommandPathException;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\entry\EiEntryConstraint;

class RestrictedEiExecution implements EiExecution {
	private $eiCommand;
	private $constraintCache;
	
	private $eiCommandPath;
	private $eiEntryConstraintGroup;
	private $comparatorConstraintGroup;

	/**
	 * @param EiCommand|null $eiCommand
	 * @param EiCommandPath $eiCommandPath
	 * @param array $eiGrantPrivileges
	 * @param PrivilegeDefinition $privilegeDefinition
	 * @param SecurityFilterDefinition $securityFilterDefinition
	 */
	public function __construct(?EiCommand $eiCommand, EiCommandPath $eiCommandPath, ConstraintCache $constraintCache) {
		$this->eiCommand = $eiCommand;
		$this->constraintCache = $constraintCache;
		
		$this->comparatorConstraintGroup = new ComparatorConstraintGroup(false);
		$this->eiEntryConstraintGroup = new EiEntryConstraintGroup(false);
		
		$this->filter($eiCommandPath);
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
	 * @see \rocket\ei\manage\security\EiExecution::getEiCommandPath()
	 */
	public function getEiCommandPath(): EiCommandPath {
		return $this->eiCommandPath;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiExecution::getEiEntryConstraint()
	 */
	public function getEiEntryConstraint() {
		return $this->eiEntryConstraintGroup;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiExecution::getCriteriaConstraint()
	 */
	public function getCriteriaConstraint() {
		return $this->comparatorConstraintGroup;
	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\manage\security\EiExecution::createEiFieldAccess()
// 	 */
// 	public function createEiFieldAccess(EiPropPath $eiPropPath) {
// 		$attributes = array();
// 		foreach ($this->eiGrantPrivileges as $eiGrantPrivilege) {
// 			$eiPropAttributes = PrivilegeDefinition::extractAttributesOfEiPropPrivilege($eiPropPath, 
// 					$eiGrantPrivilege->readEiPropPrivilegeAttributes());
// 			if ($eiPropAttributes !== null) {
// 				$attributes[] = $eiPropAttributes;
// 			}
// 		}
// 		return new RestrictedEiFieldAccess($attributes);
// 	}

	private function filter(EiCommandPath $eiCommandPath) {
		if ($this->constraintCache->getPrivilegeDefinition()->isEiCommandPathUnprivileged($eiCommandPath)) {
			$eiGrantPrivileges = $this->constraintCache->getEiGrant()->getEiGrantPrivileges()->getArrayCopy();
			
			if (empty($eiGrantPrivileges)) {
				throw new InaccessibleEiCommandPathException('EiCommandPath not accessible for current user: ' . $eiCommandPath);
			}	
			
			$this->eiCommandPath = $eiCommandPath;
			$this->refitCriteriaConstraint($eiGrantPrivileges);
			$this->refitEiEntryConstraint($eiGrantPrivileges);
			return;
		}
		
		$newEiGrantPrivileges = $this->getMatchingEiGrantPrivileges($eiCommandPath);
		
		if (empty($newEiGrantPrivileges)) {
			throw new InaccessibleEiCommandPathException('Privileged EiCommandPath not accessible for current user: ' 
					. $eiCommandPath);
		}
		
		$this->eiCommandPath = $eiCommandPath;
		$this->refitCriteriaConstraint($newEiGrantPrivileges);
		$this->refitEiEntryConstraint($newEiGrantPrivileges);
	}
	
	private function getMatchingEiGrantPrivileges(EiCommandPath $eiCommandPath) {
		$newEiGrantPrivileges = array();
		foreach ($this->constraintCache->getEiGrant()->getEiGrantPrivileges() as $eiGrantPrivilege) {
			$privilegeSetting = $eiGrantPrivilege->getPrivilegeSetting();
			if ($privilegeSetting->acceptsEiCommandPath($eiCommandPath)) {
				$newEiGrantPrivileges[] = $eiGrantPrivilege;
			}
		}
		return $newEiGrantPrivileges;
	}
	
	/**
	 * @param EiGrantPrivilege[] $filteredEiGrantPrivileges
	 */
	private function refitCriteriaConstraint(array $filteredEiGrantPrivileges) {
		$comparatorConstraints = array();
		
		$filterDefinition = $this->constraintCache->getSecurityFilterDefinition()->toFilterDefinition();
		foreach ($filteredEiGrantPrivileges as $eiGrantPrivilege) {
			if (!$eiGrantPrivilege->isRestricted()) {
				$this->comparatorConstraintGroup->setComparatorConstraints([]);
				return;
			}
			
			$comparatorConstraints[] = $filterDefinition
					->createComparatorConstraint($eiGrantPrivilege->readRestrictionFilterSettingGroup());
		}
		
		$this->comparatorConstraintGroup->setComparatorConstraints($comparatorConstraints);
	}
	
	/**
	 * @param EiGrantPrivilege[] $filteredEiGrantPrivileges
	 */
	private function refitEiEntryConstraint(array $filteredEiGrantPrivileges) {
		$eiEntryConstraints = array();
		
		foreach ($filteredEiGrantPrivileges as $eiGrantPrivilege) {
			if (!$eiGrantPrivilege->isRestricted()) {
				$this->eiEntryConstraintGroup->setEiEntryConstraints([]);
				return;
			}
			
			$eiEntryConstraints[] = $this->constraintCache->getEiEntryConstraint($eiGrantPrivilege);
		}
		
		$this->eiEntryConstraintGroup->setEiEntryConstraints($eiEntryConstraints);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiExecution::extEiCommandPath($ext)
	 */
	public function extEiCommandPath(string $ext) {
		$this->filter($this->eiCommandPath->ext($ext));
	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\manage\security\EiExecution::buildEiCommandAccessRestrictor()
// 	 */
// 	public function buildEiCommandAccessRestrictor(EiEntry $eiEntry): ?EiCommandAccessRestrictor  {
// 		$restrictor = new WhitelistEiCommandAccessRestrictor();
		
// 		foreach ($this->eiGrantPrivileges as $eiGrantPrivilege) {
// 			if ($eiGrantPrivilege->isRestricted()
// 					&& !$this->constraintCache->getrEiEntryConstraint($eiGrantPrivilege)->check($eiEntry)) {
// 				continue;
// 			}
			
// 			$restrictor->getEiCommandPaths()->addAll($eiGrantPrivilege->getEiCommandPaths());
// 		}
		
// 		if ($restrictor->getEiCommandPaths()->isEmpty()) return null;
		
// 		return $restrictor;
// 	}
}

class EiEntryConstraintGroup implements EiEntryConstraint {
	private $useAnd;
	/**
	 * @var EiEntryConstraint[]
	 */
	private $eiEntryConstraints;
	
	/**
	 * @param bool $useAnd
	 */
	function __construct(bool $useAnd) {
		$this->useAnd = $useAnd;
	}
	
	/**
	 * @param EiEntryConstraint[] $eiEntryConstraints
	 */
	function setEiEntryConstraints(array $eiEntryConstraints) {
		$this->eiEntryConstraints = $eiEntryConstraints;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiEntryConstraint::acceptsValue()
	 */
	public function acceptsValue(EiPropPath $eiPropPath, $value): bool {
		if (empty($this->eiEntryConstraints)) return true;
		
		foreach ($this->eiEntryConstraints as $eiEntryConstraint) {
			if ($eiEntryConstraint->acceptsValue($eiPropPath, $value)) {
				if (!$this->useAnd) return true;
			} else {
				if ($this->useAnd) return false;
			}
		}
		
		return $this->useAnd;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiEntryConstraint::check()
	 */
	public function check(EiEntry $eiEntry): bool {
		if (empty($this->eiEntryConstraints)) return true;
		
		foreach ($this->eiEntryConstraints as $eiEntryConstraint) {
			if ($eiEntryConstraint->check($eiEntry)) {
				if (!$this->useAnd) return true;
			} else {
				if ($this->useAnd) return false;
			}
		}
		
		return $this->useAnd;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiEntryConstraint::validate()
	 */
	public function validate(EiEntry $eiEntry) {
		foreach ($this->eiEntryConstraints as $eiEntryConstraint) {
			$eiEntryConstraint->validate($eiEntry);
		}
	}

	
	
}





