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
namespace rocket\spec\ei\component\field\impl\string\conf;

use rocket\spec\ei\component\field\impl\adapter\AdaptableEiFieldConfigurator;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\EiSetupProcess;
use rocket\spec\ei\component\field\impl\string\AlphanumericEiField;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\persistence\meta\structure\Column;
use n2n\persistence\meta\structure\StringColumn;
use n2n\util\config\LenientAttributeReader;

class AlphanumericEiFieldConfigurator extends AdaptableEiFieldConfigurator {
	const OPTION_MAXLENGTH_KEY = 'maxlength';
	
	public function __construct(AlphanumericEiField $alphanumericEiField) {
		parent::__construct($alphanumericEiField);
		
		$this->autoRegister($alphanumericEiField);
	}
	
	public function initAutoEiFieldAttributes(Column $column = null) {
		if ($column instanceof StringColumn) {
			$this->attributes->set(self::OPTION_MAXLENGTH_KEY, $column->getLength());
		}
	}
	
	public function setup(EiSetupProcess $eiSetupProcess) {
		parent::setup($eiSetupProcess);
		
		IllegalStateException::assertTrue($this->eiComponent instanceof AlphanumericEiField);
		
		if ($this->attributes->contains(self::OPTION_MAXLENGTH_KEY)) {
			$this->eiComponent->setMaxlength($this->attributes->getInt(self::OPTION_MAXLENGTH_KEY, false, null, true));
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\impl\string\conf\AlphanumericEiFieldConfigurator::createMagDispatchable($n2nContext)
	 * @return MagDispatchable
	 */
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);

		$lar = new LenientAttributeReader($this->attributes);
		
		IllegalStateException::assertTrue($this->eiComponent instanceof AlphanumericEiField);
		$magDispatchable->getMagCollection()->addMag(new NumericMag(self::OPTION_MAXLENGTH_KEY, 'Maxlength', 
				$lar->getInt(self::OPTION_MAXLENGTH_KEY, $this->eiComponent->getMaxlength())));
		
		return $magDispatchable;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$magCollection = $magDispatchable->getMagCollection();
		
		$this->attributes->set(self::OPTION_MAXLENGTH_KEY,
				$magCollection->getMagByPropertyName(self::OPTION_MAXLENGTH_KEY)->getValue());
	}
	
}
