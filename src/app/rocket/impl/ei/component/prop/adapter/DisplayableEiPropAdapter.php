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

use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\GuiProp;
use n2n\l10n\N2nLocale;
use n2n\util\ex\UnsupportedOperationException;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\field\GuiField;
use rocket\core\model\Rocket;
use rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldDisplayable;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\prop\adapter\gui\GuiFieldProxy;
use rocket\impl\ei\component\prop\adapter\gui\GuiFieldFactory;
use rocket\impl\ei\component\prop\adapter\gui\GuiProps;

abstract class DisplayableEiPropAdapter extends IndependentEiPropAdapter implements StatelessGuiFieldDisplayable, GuiEiProp, GuiFieldFactory {
	private $displayConfig;

	/**
	 * @return DisplayConfig
	 */
	protected function getDisplayConfig() {
		if ($this->displayConfig === null) {
			$this->displayConfig = new DisplayConfig(ViewMode::all());
		}

		return $this->displayConfig;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\EiProp::isPrivileged()
	 */
	public function isPrivileged(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\IndependentEiPropAdapter::createEiPropConfigurator()
	 */
	public function createEiPropConfigurator(): EiPropConfigurator {
		$configurator = parent::createEiPropConfigurator();
		IllegalStateException::assertTrue($configurator instanceof AdaptableEiPropConfigurator);
		$configurator->addAdaption($this->getDisplayConfig());
		$this->adaptConfigurator($configurator);
		return $configurator;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\GuiEiProp::buildGuiProp()
	 */
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		return GuiProps::configAndFactory($this->displayConfig, $this);
	}
	
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\manage\gui\GuiProp::buildDisplayDefinition()
// 	 */
// 	public function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
// 		$viewMode = $this->eiu->gui()->getViewMode();
// 		if (!$this->getDisplayConfig()->isViewModeCompatible($viewMode)) {
// 			return null;
// 		}
		
// 		return new DisplayDefinition($this->getSiStructureType($eiu),
// 				$this->getDisplayConfig()->isViewModeDefaultDisplayed($viewMode));
// 	}
	
// 	protected function getSiStructureType(Eiu $eiu): string {
// 		return $this->displayConfig->getSiStructureType();
// 	}
	
	public function buildGuiField(Eiu $eiu): ?GuiField {
		return new GuiFieldProxy($eiu, $this);
	}
	
// 	public function getUiOutputLabel(Eiu $eiu) {
// 		return $this->getLabelLstr()->t($eiu->getN2nLocale());
// 	}
	
	public function isStringRepresentable(): bool {
		return false;
	}
	
	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		throw new UnsupportedOperationException('EiProp ' . $this . ' not string representable.');
	}
}
