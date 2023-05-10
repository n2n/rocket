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

use rocket\user\bo\RocketUser;
use rocket\op\ei\component\command\EiCmdNature;
use rocket\op\ei\EiCmdPath;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\security\EiPermissionManager;
use rocket\op\spec\TypePath;
use rocket\user\bo\EiGrant;
use rocket\op\ei\manage\frame\Boundry;
use rocket\op\ei\manage\security\EiEntryAccess;
use rocket\op\ei\manage\ManageState;
use rocket\op\ei\manage\security\EiExecution;
use rocket\op\ei\manage\critmod\filter\ComparatorConstraintGroup;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\security\InaccessibleEiCmdPathException;
use rocket\op\ei\manage\entry\EiEntryConstraint;
use rocket\op\ei\component\command\EiCmd;

class RocketUserEiPermissionManager implements EiPermissionManager {
	/**
	 * @var RocketUser
	 */
	private $rocketUser;
	/**
	 * @var ManageState
	 */
	private $manageState;
	private $eiGrantConstraintCaches = [];
	

	/**
	 * @param RocketUser $rocketUser
	 * @param ManageState $manageState
	 */
	public function __construct(RocketUser $rocketUser, ManageState $manageState) {
		$this->rocketUser = $rocketUser;
		$this->manageState = $manageState;
	}
	
	/**
	 * @param EiMask $eimask
	 * @return EiGrantConstraintCache|null
	 */
	private function getEiGrantConstraintCache($eiMask) {
		$eiTypePathStr = (string) $eiMask->getEiTypePath();
		if (isset($this->eiGrantConstraintCaches[$eiTypePathStr])) {
			return $this->eiGrantConstraintCaches[$eiTypePathStr];
		}
		
		$eiGrant = $this->findEiGrant($eiMask->getEiTypePath());
		
		if ($eiGrant === null) {
			return null;
		}
		
		return $this->eiGrantConstraintCaches[$eiTypePathStr] = new EiGrantConstraintCache($eiGrant,
				($eiGrant->isFull() ? null : $this->managedDef->getSecurityFilterDefinition($eiMask)));
	}

	/**
	 * @param TypePath $eiTypePath
	 * @return EiGrant|NULL
	 */
	private function findEiGrant($eiTypePath) {
		foreach ($this->rocketUser->getRocketUserGroups() as $rocketUserGroup) {
			foreach ($rocketUserGroup->getEiGrants() as $eiGrant) {
				if ($eiGrant->getEiTypePath()->equals($eiTypePath)) {
					return $eiGrant;
				}
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\security\EiPermissionManager::isEiCommandAccessible()
	 */
	function isEiCommandAccessible(EiMask $contextEiMask, EiCmd $eiCmd): bool {
		if ($this->rocketUser->isAdmin()) return true;
		
		$eiMask = $eiCmd->getEiCommandCollection()->getEiMask();
		$privilegeDefinition = $this->manageState->getDef()->getPrivilegeDefinition($eiMask);
		
		$eiGrant = $this->findEiGrant($eiMask->getEiTypePath());
		return null !== $eiGrant && ($eiGrant->isFull()
				|| !$privilegeDefinition->containsEiCommand($eiCmd)
				|| $eiGrant->containsEiCmdPath(EiCmdPath::from($eiCmd)));
	}
	
	function createEiExecution(EiMask $contextEiMask, EiCmd $eiCmd): EiExecution {
		if ($this->rocketUser->isAdmin()) {
			return new FullyGrantedEiExecution($eiCmd);
		}
		
		return $this->createRestrictedEiExecution($contextEiMask, $eiCmd);
		
		
// 		$eiMask = $eiCmd->getWrapper()->getEiCommandCollection()->getEiMask();
// 		$managedDef = $manageState->getDef();
		
		
		
		
// 		$constraintCache = new EiGrantConstraintCache($eiGrant,
// 				$managedDef->getPrivilegeDefinition($eiMask),
// 				$managedDef->getSecurityFilterDefinition($eiMask));
// 		$eiEntryAccessFactory = new RestrictedEiEntryAccessFactory($constraintCache);
// 		foreach ($eiMask->getEiType()->getAllSubEiTypes() as $subEiType) {
// 			$subEiMask = $eiMask->determineEiMask($subEiType);
// 			if (null !== ($subEiGrant = $this->findEiGrant($subEiMask->getEiTypePath()))) {
// 				$eiEntryAccessFactory->addSubEiGrant(new EiGrantConstraintCache($subEiGrant,
// 						$managedDef->getPrivilegeDefinition($subEiMask),
// 						$managedDef->getSecurityFilterDefinition($subEiMask)));
// 			}
// 		}
		
// 		$eiFrame->setEiEntryAccessFactory($eiEntryAccessFactory);
		
		
		
// 		return new RestrictedEiExecution($eiCmd, 
// 				$this->createCriteriaConstraint($eiCmd, $constraintCache), 
// 				$this->createEiEntryConstraint($eiCmd, $constraintCache),
// 				$eiEntryAccessFactory);
		
// 		$eiFrame->setEiExecution($ree);
// 		$eiFrame->getBoundry()->addCriteriaConstraint(Boundry::TYPE_SECURITY, $ree->getCriteriaConstraint());
// 		$eiFrame->getBoundry()->addEiEntryConstraint(Boundry::TYPE_SECURITY, $ree->getEiEntryConstraint());
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param EiCmdNature $eiCmd
	 * @return RestrictedEiExecution
	 */
	private function createRestrictedEiExecution($contextEiMask, $eiCmd) {
		$comparatorConstraints = [];
		$eiEntryConstraints = [];
		
		foreach ($contextEiMask->getEiType()->getAllSuperEiType(true) as $eiType) {
			$eiMask = $contextEiMask->determineEiMask($eiType);
			$eiGrantConstraintCache = $this->getEiGrantConstraintCache($eiMask);
			
			if ($eiGrantConstraintCache === null) {
				continue;
			}
			
			$eiCmdAccess = $eiGrantConstraintCache->testEiCommand($eiCmd);
			if ($eiCmdAccess === null) {
				continue;
			}
			
			if (!$eiCmdAccess->isRestricted()) {
				return new RestrictedEiExecution($eiCmd, null, null);
			}
			
			array_push($comparatorConstraints, ...$eiCmdAccess->getCriteriaConstraints());
			array_push($eiEntryConstraints, ...$eiCmdAccess->getEiEntryConstraints());
		}
		
		if (empty($comparatorConstraints) || empty($eiEntryConstraints)) {
			throw new InaccessibleEiCmdPathException($eiCmd . ' inaccessible.');
		}
		
		return new RestrictedEiExecution($eiCmd,
				new ComparatorConstraintGroup(false, $comparatorConstraints), 
				new EiEntryConstraintGroup(false, $eiEntryConstraints));		
	}
	
	private function createEiEntryAccessFactory($contextEiMask, $eiCmd) {
		$eiEntryAccessFactory = new RestrictedEiEntryAccessFactory();
		
		foreach ($contextEiMask->getEiType()->getAllSuperEiTypes(true) as $eiType) {
			$eiMask = $contextEiMask->determineEiMask($eiType);
			if (null !== ($eiGrantConstraintCache = $this->getEiGrantConstraintCache($eiMask))) {
				$eiEntryAccessFactory->addEiGrantConstraintCache($eiGrantConstraintCache);
			}
		}
		
		foreach ($contextEiMask->getEiType()->getAllSubEiTypes(false) as $eiType) {
			$eiMask = $contextEiMask->determineEiMask($eiType);
			if (null !== ($eiGrantConstraintCache = $this->getEiGrantConstraintCache($eiMask))) {
				$eiEntryAccessFactory->addEiGrantConstraintCache($eiGrantConstraintCache);
			}
		}
		
		return $eiEntryAccessFactory;
	}
}



class RestrictedEiEntryAccess implements EiEntryAccess {
	/**
	 * @var EiEntryConstraint
	 */
	private $eiEntryConstraint;
	/**
	 * @var EiPropPath[]
	 */
	private $writableEiPropPaths;
	/**
	 * @var EiCmdPath[]
	 */
	private $executableEiCmdPaths;
	
	/**
	 * @param EiPropPath[] $writableEiPropPaths
	 * @param EiCmdPath[] $executableEiCmdPaths
	 */
	function __construct(EiEntryConstraint $eiEntryConstraint, array $writableEiPropPaths, array $executableEiCmdPaths) {
		$this->eiEntryConstraint = $eiEntryConstraint;
		
		foreach ($writableEiPropPaths as $writableEiPropPath) {
			$this->writableEiPropPaths[(string) $writableEiPropPath] = $writableEiPropPath;
		}
		
		foreach ($executableEiCmdPaths as $executableEiCmdPath) {
			$this->executableEiCmdPaths[(string) $executableEiCmdPath] = $executableEiCmdPath;
		}
	}
	
	function getEiEntryConstraint(): ?EiEntryConstraint {
		return $this->eiEntryConstraint;
	}
	
	function isEiPropWritable(EiPropPath $eiPropPath): bool {
		return isset($this->writableEiPropPaths[(string) $eiPropPath]);
	}

	function isEiCommandExecutable(EiCmdPath $eiCmdPath): bool {
		return isset($this->executableEiCmdPaths[(string) $eiCmdPath]);
	}
}
