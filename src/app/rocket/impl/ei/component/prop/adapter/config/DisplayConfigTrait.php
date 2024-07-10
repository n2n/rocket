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
 * Andreas von Burg...........: Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\impl\ei\component\prop\adapter\config;

use rocket\op\ei\manage\gui\EiGuiProp;
use rocket\op\ei\util\Eiu;
use rocket\ui\gui\ViewMode;
use rocket\ui\gui\field\GuiField;
use rocket\op\ei\util\factory\EifGuiField;
use n2n\util\ex\UnsupportedOperationException;
use rocket\ui\gui\field\BackableGuiField;

trait DisplayConfigTrait {
	protected DisplayConfig $displayConfig;

	/**
	 *
	 * @return DisplayConfig
	 */
	function getDisplayConfig(): DisplayConfig {
		if (!isset($this->displayConfig)) {
			$this->displayConfig = new DisplayConfig(ViewMode::all());
		}

		return $this->displayConfig;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \rocket\op\ei\component\prop\EiPropNature::isPrivileged()
	 */
	public function isPrivileged(): bool {
		return false;
	}

	
	function buildGuiProp(Eiu $eiu): ?EiGuiProp {
		return $eiu->factory ()->newGuiProp(function (Eiu $eiu) {
			$displayConfig = $this->getDisplayConfig();
			return $eiu->f()->newGuiProp()
					->setDefaultDisplayed($displayConfig->isViewModeDefaultDisplayed($eiu->guiDefinition()->getViewMode()))
					->setSiStructureType($displayConfig->getSiStructureType())
					->toGuiProp();
		})->toEiGuiProp();
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		return $this->createOutGuiField($eiu);
	}

	protected function createOutGuiField(Eiu $eiu): BackableGuiField {
		throw new UnsupportedOperationException ( get_class ($this) . ' must implement either'
				. ' createOutGuiField(Eiu $eiu): BackableGuiField or'
				. ' buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField.' );
	}
}
