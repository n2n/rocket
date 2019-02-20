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
use rocket\ei\component\command\EiCommand;
use rocket\ei\EiCommandPath;
use rocket\ei\EiPropPath;
use rocket\ei\manage\security\EiFieldAccess;
use rocket\ei\manage\security\EiPermissionManager;
use rocket\spec\TypePath;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\security\InaccessibleEiCommandPathException;
use rocket\user\bo\EiGrant;
use rocket\ei\manage\frame\Boundry;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\security\EiEntryAccess;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\attrs\Attributes;
use rocket\ei\manage\security\privilege\PrivilegeDefinition;
use rocket\ei\manage\security\privilege\data\PrivilegeSetting;
use rocket\ei\manage\security\EiEntryAccessFactory;
use rocket\ei\manage\ManageState;

class RocketUserEiPermissionManager implements EiPermissionManager {
	private $rocketUser;

	public function __construct(RocketUser $rocketUser) {
		$this->rocketUser = $rocketUser;
	}

	/**
	 * @param TypePath $eiTypePath
	 * @return EiGrant|NULL
	 */
	private function findEiGrant(TypePath $eiTypePath) {
		foreach ($this->rocketUser->getRocketUserGroups() as $rocketUserGroup) {
			foreach ($rocketUserGroup->getEiGrants() as $eiGrant) {
				if ($eiGrant->getEiTypePath()->equals($eiTypePath)) {
					return $eiGrant;
				}
			}
		}

		return null;
	}


	public function isEiCommandAccessible(EiCommand $eiCommand, ManageState $manageState): bool {
		if ($this->rocketUser->isAdmin()) return true;
		
		$eiMask = $eiCommand->getWrapper()->getEiCommandCollection()->getEiMask();
		$eiGrant = $this->findEiGrant($eiMask->getEiTypePath());
		$eiCommandPath = EiCommandPath::from($eiCommand);
		return null !== $eiGrant && ($eiGrant->isFull()
				|| $manageState->getDef()->getPrivilegeDefinition($eiMask)->isEiCommandPathUnprivileged($eiCommandPath)
				|| $eiGrant->containsEiCommandPath($eiCommandPath));
	}

	function applyToEiFrame(EiFrame $eiFrame, EiCommandPath $eiCommandPath) {
		$eiMask = $eiFrame->getContextEiEngine()->getEiMask();
		$eiCommand = null;
		
		if (!$eiCommandPath->isEmpty()) {
			$eiCommand = $eiMask->getEiCommandCollection()->getById($eiCommandPath->getFirstId());
		}
				
		if ($this->rocketUser->isAdmin()) {
			$eiFrame->setEiExecution(new FullyGrantedEiExecution($eiCommandPath, $eiCommand));
			$eiFrame->setEiEntryAccessFactory(new FullEiEntryAccessFactory());
			return;
		}
		
		$eiGrant = $this->findEiGrant($eiMask->getEiTypePath());
		if ($eiGrant === null) {
			throw new InaccessibleEiCommandPathException();
		}
		
		$managedDef = $eiFrame->getManageState()->getDef();
		
		$constraintCache = new ConstraintCache($eiGrant,
				$managedDef->getPrivilegeDefinition($eiMask),
				$managedDef->getSecurityFilterDefinition($eiMask));
		$eiEntryAccessFactory = new RestrictedEiEntryAccessFactory($constraintCache);
		foreach ($eiMask->getEiType()->getAllSubEiTypes() as $subEiType) {
			$subEiMask = $eiFrame->determineEiMask($subEiType);
			if (null !== ($subEiGrant = $this->findEiGrant($subEiMask->getEiTypePath()))) {
				$eiEntryAccessFactory->addSubEiGrant(new ConstraintCache($subEiGrant,
						$managedDef->getPrivilegeDefinition($subEiMask),
						$managedDef->getSecurityFilterDefinition($subEiMask)));
			}
		}
		
		$eiFrame->setEiEntryAccessFactory($eiEntryAccessFactory);
		
		if ($eiGrant->isFull()) {
			$eiFrame->setEiExecution(new FullyGrantedEiExecution($eiCommandPath, $eiCommand));
			return;
		}
		
		$ree = new RestrictedEiExecution($eiCommand, $eiCommandPath, $constraintCache, $eiEntryAccessFactory);
		$eiFrame->setEiExecution($ree);
		$eiFrame->getBoundry()->addCriteriaConstraint(Boundry::TYPE_SECURITY, $ree->getCriteriaConstraint());
		$eiFrame->getBoundry()->addEiEntryConstraint(Boundry::TYPE_SECURITY, $ree->getEiEntryConstraint());
	}
	
	
// 	public function createEiEntryAccess(EiEntry $eiEntry, N2nContext $n2nContext) {
// 		$privilegeDefinition = $eiEntry->getEiMask()->getEiEngine()->createPrivilegeDefinition($n2nContext);
		
		
// 		if ($this->findEiGrant($eiEntry->getEiMask()->getEiTypePath())
		
// 		return new RestrictedEiEntryAccess($privilegeDefinition);
		
		
// 	}
}

class FullEiEntryAccessFactory implements EiEntryAccessFactory {
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiEntryAccessFactory::createEiEntryAccess()
	 */
	function createEiEntryAccess(EiEntry $eiEntry): EiEntryAccess {
		return new StaticEiEntryAccess(true);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiEntryAccessFactory::isExecutableBy()
	 */
	function isExecutableBy(EiCommandPath $eiCommandPath): bool {
		return true;
	}

}


// class RestrictedEiEntryAccess implements EiEntryAccess {
// 	public function __construct(PrivilegeDefinition $privilegeDefinition) {
// 		$this->filterDefinition = $
// 	}
	
// 	public function getEiFieldAccess(EiPropPath $eiPropPath): EiFieldAccess {
// 	}
	
// 	public function isExecutableBy(EiCommandPath $eiCommandPath) {
		
		
// 	}
	
	
// }


class StaticEiEntryAccess implements EiEntryAccess {
	private $granted;
	
	public function __construct(bool $granted) {
		$this->granted = $granted;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiEntryAccess::getEiFieldAccess()
	 */
	public function getEiFieldAccess(EiPropPath $eiPropPath): EiFieldAccess {
		return new CommonEiFieldAccess($this->granted, $this->granted ? null : array());
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiEntryAccess::isExecutableBy()
	 */
	public function isExecutableBy(EiCommandPath $eiCommandPath): bool {
		return true;
	}
}

class RestrictedEiEntryAccess implements EiEntryAccess {
	private $privilegeDefinition;
	private $privilegeSettings;
	
	/**
	 * @param PrivilegeDefinition $privilegeDefinition
	 * @param PrivilegeSetting[] $privilegeSettings
	 */
	function __construct(PrivilegeDefinition $privilegeDefinition, array $privilegeSettings) {
		$this->privilegeDefinition = $privilegeDefinition;
		$this->privilegeSettings = $privilegeSettings;
	}
	
	function isExecutableBy(EiCommandPath $eiCommandPath): bool {
		if ($this->privilegeDefinition->isEiCommandPathUnprivileged($eiCommandPath)) {
			return true;
		}
		
		foreach ($this->privilegeSettings as $privilegeSetting) {
			if ($privilegeSetting->acceptsEiCommandPath($eiCommandPath)) {
				return true;
			}
		}
		
		return false;
	}
	
	function getEiFieldAccess(EiPropPath $eiPropPath): EiFieldAccess {
		$attributes = array();
		foreach ($this->privilegeSettings as $privilegeSetting) {
			$eiPropAttributes = $privilegeSetting->getEiPropAttributes($eiPropPath);
			if ($eiPropAttributes !== null) {
				$attributes[] = $eiPropAttributes;
			}
		}
		return new CommonEiFieldAccess(false, $attributes);
	}
}

class CommonEiFieldAccess implements EiFieldAccess {
	private $fullyGranted;
	private $attributes;
	
	/**
	 * @param bool $fullyGranted
	 * @param Attributes[] $attributes
	 */
	public function __construct(bool $fullyGranted, ?array $attributes) {
		ArgUtils::assertTrue($fullyGranted === ($attributes === null));
		ArgUtils::valArray($attributes, Attributes::class, true);
		$this->fullyGranted = $fullyGranted;
		$this->attributes = $attributes;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiFieldAccess::isFullyGranted()
	 */
	public function isFullyGranted(): bool {
		return $this->fullyGranted;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiFieldAccess::getAttributes()
	 */
	public function getAttributes(): array {
		IllegalStateException::assertTrue($this->attributes !== null);
		return $this->attributes;
	}
}