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

use rocket\ei\component\prop\PrivilegedEiProp;
use rocket\core\model\Rocket;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\ei\util\factory\EifField;
use n2n\validation\validator\impl\Validators;
use rocket\ei\util\factory\EifGuiField;
use n2n\util\ex\UnsupportedOperationException;

abstract class EditablePropertyEiPropNatureNatureAdapter extends DisplayablePropertyEiPropNatureNatureAdapter implements PrivilegedEiProp {
	use EditConfig;

//	private ?EditConfig $editConfig;

	function isPrivileged(): bool {
		return true;
	}

//	/**
//	 * @return EditConfig
//	 */
//	protected function getEditConfig() {
//		if ($this->editConfig === null) {
//			$this->editConfig = new EditConfig();
//		}
//
//		return $this->editConfig;
//	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\DisplayablePropertyEiPropNatureNatureAdapter::createEifField()
	 */
	protected function createEifField(Eiu $eiu): EifField {
		$eifField = parent::createEifField($eiu);
		
		if (!$this->getEditConfig()->isReadOnly()) {
			$eifField->setWriter(function ($value) use ($eiu) {
				$eiu->entry()->writeNativeValue($this, $value);
			});
		}
		
		$eifField->setCopier(function ($value) {
			return $value;
		});
				
		if ($this->getEditConfig()->isMandatory()) {
			$eifField->val(Validators::mandatory());
		}
		
		return $eifField;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\DisplayablePropertyEiPropNatureNatureAdapter::buildGuiField()
	 */
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		if ($readOnly || $eiu->guiFrame()->isReadOnly()
				|| ($eiu->entry()->isNew() && $this->getEditConfig()->isConstant())) {
			return $this->createOutEifGuiField($eiu)->toGuiField();
		}
		
		return $this->createInEifGuiField($eiu)->toGuiField();
	}
	
	/**
	 * @param Eiu $eiu
	 * @return EifGuiField
	 */
	protected function createOutEifGuiField(Eiu $eiu): EifGuiField {
		throw new UnsupportedOperationException(get_class($this)
				. ' must implement either'
				. ' createOutEifGuiField(Eiu $eiu): EifGuiField  (recommended) or'
				. ' buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField.');
	}
	
	/**
	 * @param Eiu $eiu
	 * @return EifGuiField
	 */
	protected function createInEifGuiField(Eiu $eiu): EifGuiField {
		throw new UnsupportedOperationException(get_class($this)
				. ' must implement either'
				. ' createInEifGuiField(Eiu $eiu): EifGuiField  (recommended) or'
				. ' buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField.');
	}
}
