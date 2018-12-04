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
namespace rocket\impl\ei\component\prop\bool\conf;

use rocket\ei\component\EiSetup;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\prop\bool\OnlineEiProp;
use rocket\impl\ei\component\prop\bool\command\OnlineEiCommand;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\core\container\N2nContext;
use n2n\persistence\meta\structure\Column;

class OnlineEiPropConfigurator extends AdaptableEiPropConfigurator {
	const COMMON_ONLINE_PROP_NAME = 'online';
	
	public function __construct(OnlineEiProp $onlineEiProp) {
		parent::__construct($onlineEiProp);

		$this->autoRegister($onlineEiProp);
		
		$this->addMandatory = false;
		$this->addConstant = false;
	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$level = parent::testCompatibility($propertyAssignation);
		
		if (!$level) return $level;
		
		if ($this->requirePropertyName() == self::COMMON_ONLINE_PROP_NAME) {
			return CompatibilityLevel::COMMON;
		}
		
		return $level;
	}
	
	
	public function initAutoEiPropAttributes(N2nContext $n2nContext, Column $column = null) {
		parent::initAutoEiPropAttributes($n2nContext, $column);
		
		$this->attributes->set(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY, false);
		$this->attributes->set(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY, false);
		$this->attributes->set(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, false);
	}
	
	public function setup(EiSetup $setupProcess) {
		parent::setup($setupProcess);
		
		$onlineEiProp = $this->eiComponent;
		IllegalStateException::assertTrue($onlineEiProp instanceof OnlineEiProp);
		
		$onlineEiCommand = new OnlineEiCommand();
		$onlineEiCommand->setOnlineEiProp($onlineEiProp);
		
		$setupProcess->eiu()->mask()->addEiCommand($onlineEiCommand, true);
	}
}
