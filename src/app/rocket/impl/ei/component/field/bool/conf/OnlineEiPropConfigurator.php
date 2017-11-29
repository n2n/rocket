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
namespace rocket\impl\ei\component\field\bool\conf;

use rocket\spec\ei\component\EiSetupProcess;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\field\adapter\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\field\bool\OnlineEiProp;
use rocket\impl\ei\component\field\bool\command\OnlineEiCommand;
use rocket\spec\ei\component\field\indepenent\PropertyAssignation;
use rocket\spec\ei\component\field\indepenent\CompatibilityLevel;

class OnlineEiPropConfigurator extends AdaptableEiPropConfigurator {
	const COMMON_ONLINE_PROP_NAME = 'online';
	
	public function __construct(OnlineEiProp $onlineEiProp) {
		parent::__construct($onlineEiProp);

		$this->addMandatory = false;
		$this->addConstant = false;
		$this->autoRegister($onlineEiProp);
	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$level = parent::testCompatibility($propertyAssignation);
		
		if (!$level) return $level;
		
		if ($propertyAssignation->hasEntityProperty()
				&& $propertyAssignation->getEntityProperty()->getName() == self::COMMON_ONLINE_PROP_NAME) {
			return CompatibilityLevel::COMMON;
		}
		
		return $level;
	}
	
	public function setup(EiSetupProcess $setupProcess) {
		parent::setup($setupProcess);
		
		$onlineEiProp = $this->eiComponent;
		IllegalStateException::assertTrue($onlineEiProp instanceof OnlineEiProp);
		
		$onlineEiCommand = new OnlineEiCommand();
		$onlineEiCommand->setOnlineEiProp($onlineEiProp);
		
		$setupProcess->getEiCommandCollection()->add($onlineEiCommand);
	}
}
