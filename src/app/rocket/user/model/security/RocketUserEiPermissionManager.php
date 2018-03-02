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
use rocket\spec\ei\security\EiPermissionManager;
use rocket\spec\ei\EiType;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\security\InaccessibleControlException;
use rocket\spec\ei\component\command\EiCommand;
use rocket\spec\ei\security\EiExecution;
use rocket\spec\ei\EiCommandPath;
use n2n\core\container\N2nContext;

class RocketUserEiPermissionManager implements EiPermissionManager {
	private $rocketUser;

	public function __construct(RocketUser $rocketUser) {
		$this->rocketUser = $rocketUser;
	}

	private function findEiGrant(EiType $eiType, EiMask $eiMask = null) {
		$eiTypeId = $eiType->getId();
		$eiMaskId = null;
		if (null !== $eiMask) {
			$eiMaskId = $eiMask->getExtension()->getId();
		}

		foreach ($this->rocketUser->getRocketUserGroups() as $rocketUserGroup) {
			foreach ($rocketUserGroup->getEiGrants() as $eiGrant) {
				if ($eiGrant->getEiTypeId() === $eiTypeId && $eiGrant->getEiMaskId() === $eiMaskId) {
					return $eiGrant;
				}
			}
		}

		return null;
	}


	public function isEiCommandAccessible(EiCommand $eiCommand): bool {
		if ($this->rocketUser->isAdmin()) return true;

		return null !== $this->findEiGrant($eiCommand->getEiMask()->getEiType(), $eiCommand->getEiMask()->getEiMask());
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\security\EiPermissionManager::createUnboundEiExceution($eiMask, $commandPath)
	 */
	public function createUnboundEiExceution(EiMask $eiMask, EiCommandPath $commandPath, N2nContext $n2nContext): EiExecution {
		return $this->buildEiExecution($n2nContext, $eiMask, $commandPath);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\security\EiPermissionManager::createEiExecution()
	 */
	public function createEiExecution(EiCommand $eiCommand, N2nContext $n2nContext): EiExecution {
		return $this->buildEiExecution($n2nContext, $eiCommand->getEiMask(), 
				EiCommandPath::from($eiCommand), $eiCommand);
	}

	private function buildEiExecution(N2nContext $n2nContext, EiMask $eiMask, 
			EiCommandPath $eiCommandPath, EiCommand $eiCommand = null): EiExecution {
		if ($this->rocketUser->isAdmin()) {
			return new FullyGrantedEiExecution($eiCommandPath, $eiCommand);
		}
		
		$eiGrant = $this->findEiGrant($eiMask->getEiType(), $eiMask);

		if ($eiGrant === null) {
			throw new InaccessibleControlException();
		}

		if ($eiGrant->isFull()) {
			return new FullyGrantedEiExecution($eiCommandPath, $eiCommand);
		}
		
		return new RestrictedEiExecution($eiCommand, $eiCommandPath, 
				$eiGrant->getEiPrivilegeGrants()->getArrayCopy(),
				$eiMask->createPrivilegeDefinition($n2nContext),
				$eiMask->createEiEntryFilterDefinition($n2nContext));
	}
}
