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
namespace rocket\impl\ei\component\prop\adapter;

use n2n\l10n\N2nLocale;
use n2n\util\ex\UnsupportedOperationException;
use rocket\ei\component\prop\FieldEiProp;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\impl\ei\component\prop\adapter\entry\Readable;
use rocket\impl\ei\component\prop\adapter\entry\SimpleEiField;
use rocket\impl\ei\component\prop\adapter\gui\GuiFieldFactory;
use rocket\impl\ei\component\prop\adapter\gui\GuiFieldProxy;
use rocket\impl\ei\component\prop\adapter\gui\GuiProps;
use rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldDisplayable;

abstract class PropertyDisplayableEiPropAdapter extends PropertyEiPropAdapter implements StatelessGuiFieldDisplayable, 
		FieldEiProp, GuiEiProp, GuiFieldFactory, Readable {
	private $displayConfig;

	/**
	 * @return DisplayConfig
	 */
	protected function getDisplayConfig(): DisplayConfig {
		if ($this->displayConfig !== null) {
			return $this->displayConfig;
		}
		
		return $this->displayConfig = new DisplayConfig(ViewMode::all());
	}

	public function createConfigurator(): AdaptableEiPropConfigurator {
		return parent::createConfigurator()->addAdaption($this->getDisplayConfig());
	}
	
	public function isEiField(): bool {
		return true;
	}
	
	public function buildEiField(Eiu $eiu): ?EiField {
		return new SimpleEiField($eiu, 
				$this->getObjectPropertyAccessProxy()->getConstraint()->getLenientCopy(), 
				$this);
	}
	
	
	public function read(Eiu $eiu) {
		return $eiu->entry()->readNativValue($this);
	}
	
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		return GuiProps::configAndFactory($this->getDisplayConfig(), $this);
	}
	
	public function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		return new GuiFieldProxy($eiu, $this);
	}
	
	public function isStringRepresentable(): bool {
		return false;
	}
	
	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		throw new UnsupportedOperationException('EiProp ' . $this->id . ' not summarizable.');
	}
}
