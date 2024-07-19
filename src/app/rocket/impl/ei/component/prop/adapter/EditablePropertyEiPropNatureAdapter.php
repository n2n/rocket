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

use rocket\op\ei\component\prop\PrivilegedEiProp;
use rocket\op\ei\util\Eiu;
use rocket\ui\gui\field\GuiField;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\op\ei\util\factory\EifGuiField;
use n2n\util\ex\UnsupportedOperationException;
use rocket\ui\gui\field\BackableGuiField;
use rocket\op\ei\manage\gui\EiGuiProp;
use rocket\impl\ei\component\prop\adapter\trait\WritableEiFieldTrait;
use rocket\impl\ei\component\prop\adapter\trait\OutEiGuiPropTrait;
use rocket\impl\ei\component\prop\adapter\trait\InEiGuiPropTrait;

abstract class EditablePropertyEiPropNatureAdapter extends DisplayablePropertyEiPropNatureAdapter implements PrivilegedEiProp {
	use WritableEiFieldTrait, InEiGuiPropTrait;

	function isPrivileged(): bool {
		return true;
	}



//	/**
//	 * {@inheritDoc}
//	 * @see \rocket\impl\ei\component\prop\adapter\DisplayablePropertyEiPropAdapter::buildGuiField()
//	 */


//	/**
//	 * @param Eiu $eiu
//	 * @return EifGuiField
//	 */
//	protected function buildOutGuiField(Eiu $eiu): ?BackableGuiField {
//		throw new UnsupportedOperationException(get_class($this)
//				. ' must implement either'
//				. ' buildOutGuiField(Eiu $eiu): ?BackableGuiField  (recommended) or'
//				. ' buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField.');
//	}

//	/**
//	 * @param Eiu $eiu
//	 * @return EifGuiField
//	 */
//	protected function buildInGuiField(Eiu $eiu): ?BackableGuiField {
//		throw new UnsupportedOperationException(get_class($this)
//				. ' must implement either'
//				. ' buildInGuiField(Eiu $eiu): ?BackableGuiField  (recommended) or'
//				. ' buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField.');
//	}
}
