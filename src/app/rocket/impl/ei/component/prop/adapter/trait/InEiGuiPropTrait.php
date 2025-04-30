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

namespace rocket\impl\ei\component\prop\adapter\trait;

use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\gui\EiGuiProp;
use rocket\ui\gui\field\BackableGuiField;
use n2n\util\ex\UnsupportedOperationException;
use rocket\ui\gui\field\GuiField;

trait InEiGuiPropTrait {
	use OutEiGuiPropTrait, EditConfigTrait;

	function buildEiGuiProp(Eiu $eiu): ?EiGuiProp {
		$displayConfig = $this->getDisplayConfig();
		if (!$displayConfig->isViewModeCompatible($eiu->guiDefinition()->getViewMode())) {
			return null;
		}

		return $eiu->factory()
				->newGuiProp(fn (Eiu $eiu, bool $readOnly) => $this->buildGuiField($eiu, $readOnly))
				->setDefaultDisplayed($displayConfig->isViewModeDefaultDisplayed($eiu->guiDefinition()->getViewMode()))
				->setSiStructureType($displayConfig->getSiStructureType())
				->toEiGuiProp();
	}

	protected function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		if ($readOnly || $this->isReadOnly() || $eiu->guiDefinition()->isReadOnly()
				|| ($eiu->entry()->isNew() && $this->isConstant())) {
			$guiField = $this->buildOutGuiField($eiu);
		} else {
			$guiField = $this->buildInGuiField($eiu);
		}

		if ($guiField->getModel() === null) {
			$guiField->setModel($eiu->field()->asGuiFieldModel());
		}

		return $guiField;
	}

//	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
//		return $this->createOutGuiField($eiu);
//	}

	protected function buildInGuiField(Eiu $eiu): ?BackableGuiField {
		throw new UnsupportedOperationException ( get_class ($this) . ' must implement either'
				. ' buildInGuiField(Eiu $eiu): ?BackableGuiField or'
				. ' buildGuiProp(Eiu $eiu): ?EiGuiProp.' );
	}
}