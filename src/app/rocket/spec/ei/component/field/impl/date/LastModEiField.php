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
namespace rocket\spec\ei\component\field\impl\date;

use rocket\spec\ei\component\EiSetupProcess;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
use rocket\spec\ei\component\modificator\impl\date\LastModEiModificator;
use rocket\spec\ei\component\field\impl\DisplayableEiFieldAdapter;
use n2n\dispatch\mag\MagCollection;
use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\property\impl\DateTimeEntityProperty;
use n2n\ui\view\impl\html\HtmlView;
use n2n\l10n\DateTimeFormat;
use n2n\util\config\Attributes;

// class LastModEiField extends GuiEiFieldAdapter {
	
// 	public function __construct(Attributes $attributes) {
// 		parent::__construct($attributes);
// 		$this->displayInAddViewEnabled = false;
// 		$this->displayInEditViewEnabled = false;
// 		$this->displayInOverviewEnabled = false;
// 	}
	
// 	public function getTypeName(): string {
// 		return 'Last Mod';
// 	}
	
// 	public function isRequired(EiMapping $eiMapping, EntrySourceInfo $entrySourceInfo) {
// 		return false;
// 	}
	
// 	public function setup(SetupProcess $setupProcess) {
// 		parent::setup($setupProcess);
// 		$setupProcess->getEiSpec()->getEiModificatorCollection()->add(new LastModEiModificator($this));
// 	}
	
// 	public function createMagCollection() {
// 		$magCollection = new MagCollection();
// 		$this->applyDisplayOptions($magCollection, true, true, false, false, false);
// 		return $magCollection;
// 	}
	

// 	public function isCompatibleWith(EntityProperty $entityProperty) {
// 		return $entityProperty instanceof DateTimeEntityProperty;
// 	}

// 	public function createOutputUiComponent(HtmlView $view, 
// 			EntrySourceInfo $entrySourceInfo) {
// 		return $view->getHtmlBuilder()->getL10nDateTime($eiMapping->getValue($this->getId()), 
// 				DateTimeFormat::STYLE_MEDIUM, DateTimeFormat::STYLE_MEDIUM);
		
// 	}
	
// }
