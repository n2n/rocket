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

use rocket\op\ei\manage\security\EiEntryAccess;
use rocket\op\ei\manage\security\EiExecution;
use rocket\op\ei\component\command\EiCmdNature;
use rocket\op\ei\manage\security\filter\SecurityFilterDefinition;
use rocket\op\ei\EiCmdPath;
use rocket\op\ei\manage\security\privilege\PrivilegeDefinition;
use rocket\op\ei\manage\frame\CriteriaConstraint;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\entry\EiEntryConstraint;
use rocket\op\ei\manage\critmod\filter\ComparatorConstraint;

class RestrictedEiExecution implements EiExecution {
	private $eiCmd;
	private $comparatorConstraint;
	private $eiEntryConstraint;
	private $restrictedEiEntryAccessFactory;

	/**
	 * @param EiCmdNature|null $eiCmd
	 * @param EiCmdPath $eiCmdPath
	 * @param array $eiGrantPrivileges
	 * @param PrivilegeDefinition $privilegeDefinition
	 * @param SecurityFilterDefinition $securityFilterDefinition
	 */
	function __construct(EiCmdNature $eiCmd, ?ComparatorConstraint $comparatorConstraint, ?EiEntryConstraint $eiEntryConstraint,
			RestrictedEiEntryAccessFactory $restrictedEiEntryAccessFactory) {
		$this->eiCmd = $eiCmd;
		$this->comparatorConstraint = $comparatorConstraint;
		$this->eiEntryConstraint = $eiEntryConstraint;
		$this->restrictedEiEntryAccessFactory = $restrictedEiEntryAccessFactory;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\security\EiExecution::getEiCmd()
	 */
	function getEiCmd(): EiCmdNature {
		return $this->eiCmd;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\security\EiExecution::getCriteriaConstraint()
	 */
	function getCriteriaConstraint(): ?CriteriaConstraint {
		return $this->comparatorConstraint;
	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\op\ei\manage\security\EiExecution::getEiEntryConstraint()
// 	 */
// 	function getEiEntryConstraint(): ?EiEntryConstraint {
// 		return $this->eiEntryConstraint;
// 	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\security\EiExecution::createEiEntryAccess()
	 */
	function createEiEntryAccess(EiEntry $eiEntry): EiEntryAccess {
		return $this->restrictedEiEntryAccessFactory->createEiEntryAccess($this->eiEntryConstraint, $eiEntry);
	}
}
