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
namespace rocket\impl\ei\component\mod\adapter;

use rocket\op\ei\component\modificator\EiModNature;
use rocket\op\ei\component\modificator\IndependentEiModNature;
use rocket\op\ei\component\EiConfigurator;
use rocket\impl\ei\component\DefaultEiConfigurator;
use rocket\op\ei\util\Eiu;

abstract class IndependentEiModificatorAdapter extends EiModNatureAdapter implements IndependentEiModNature {
	
	public function __construct() {
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\modificator\IndependentEiModNature::createEiConfigurator()
	 */
	public function createEiConfigurator(): EiConfigurator {
		return new DefaultEiConfigurator($this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\mod\adapter\EiModNatureAdapter::equals()
	 */
	public function equals($obj) {
		return $obj instanceof EiModNature && parent::equals($obj);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\modificator\EiModNature::setupEiFrame()
	 */
	public function setupEiFrame(Eiu $eiu) { }
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\modificator\EiModNature::setupEiEntry()
	 */
	public function setupEiEntry(Eiu $eiu) { }
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\modificator\EiModNature::setupGuiDefinition()
	 */
	public function setupGuiDefinition(Eiu $eiu) { }
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\modificator\EiModNature::setupEiEntryGui()
	 */
	public function setupEiEntryGui(\rocket\op\ei\manage\gui\EiEntryGui $eiEntryGui) { }
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\modificator\EiModNature::setupDraftDefinition()
	 */
	public function setupDraftDefinition(\rocket\op\ei\manage\draft\DraftDefinition $draftDefinition) { }
}