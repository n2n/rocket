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
namespace rocket\spec\ei\component\command\impl\common;

use rocket\spec\ei\component\command\GenericOverviewEiCommand;
use rocket\spec\ei\component\command\impl\IndependentEiCommandAdapter;
use rocket\spec\ei\component\impl\EiConfiguratorAdapter;
use n2n\core\container\N2nContext;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\command\impl\common\controller\OverviewController;
use rocket\spec\ei\component\EiConfigurator;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\spec\ei\manage\util\model\Eiu;
use n2n\web\http\controller\Controller;
use rocket\spec\ei\component\EiSetupProcess;
use n2n\reflection\CastUtils;

class OverviewEiCommand extends IndependentEiCommandAdapter implements GenericOverviewEiCommand {
	const ID_BASE = 'overview';
	
	private $pageSize = 30;
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function isOverviewAvaialble(): bool {
		return true;
	}
	
	public function getOverviewUrlExt() {
		return null;
	}
	
	public function getTypeName(): string {
		return 'Overview (Rocket)';
	}
	
	public function lookupController(Eiu $eiu): Controller {
		return new OverviewController($this->pageSize);
	}
	
	public function createEiConfigurator(): EiConfigurator {
		return new ListEiConfigurator($this);
	}
	
	public function getPageSize() {
		return $this->pageSize;
	}
	
	public function setPageSize($pageSize) {
		$this->pageSize = $pageSize;
	}
}

class ListEiConfigurator extends EiConfiguratorAdapter {
	const OPTION_PAGE_SIZE_KEY = 'pageSize';
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$eiComponent = $this->eiComponent;
		IllegalStateException::assertTrue($eiComponent instanceof OverviewEiCommand);
		
		$magCollection = new MagCollection();
		$magCollection->addMag(new NumericMag(self::OPTION_PAGE_SIZE_KEY, 
				'Num Entries', $this->getAttributes()->get(
						self::OPTION_PAGE_SIZE_KEY, false, $eiComponent->getPageSize())));
		return new MagForm($magCollection);
	}
	
	public function setup(EiSetupProcess $eiSetupProcess) {
		$eiComponent = $this->eiComponent;
	    CastUtils::assertTrue($eiComponent instanceof OverviewEiCommand);

	    $eiComponent->setPageSize($this->attributes->getInt(self::OPTION_PAGE_SIZE_KEY, false, 
	           $eiComponent->getPageSize()));
	}
}
