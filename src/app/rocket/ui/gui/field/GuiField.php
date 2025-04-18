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
namespace rocket\ui\gui\field;

use rocket\ui\si\content\SiField;
use n2n\core\container\N2nContext;
use n2n\util\ex\UnsupportedOperationException;

interface GuiField {
	
// 	/**
// 	 * @return GuiFieldDisplayable
// 	 */
// 	public function getDisplayable(): GuiFieldDisplayable;

// 	/**
// 	 * @return boolean 
// 	 */
// 	public function isReadOnly(): bool;
	
	function getSiField(): ?SiField;

//	function handleSiFieldInput(SiFieldInput $siFieldInput): bool;

	/**
	 * An {@link UnsupportedOperationException} might be thrown if the field does not support values.
	 *
	 * @return mixed
	 *
	 */
	function getValue(): mixed;

//	function prepareForSave(N2nContext $n2nContext): bool;

//	/**
//	 * Saves/writes the value previously read value by {@link self::prepareForSave()} to the target value container
//	 * (e. g. {@link EiEntry})
//	 *
//	 * @throws \n2n\util\ex\IllegalStateException if {@link self::getSiField()::isReadOnly()} returns true or
//	 * 		{@link self::readSiAndValidate()} has never been called.
//	 */
//	function save(N2nContext $n2nContext): void;
	
	/**
	 * @return GuiFieldMap|NULL
	 */
	function getForkGuiFieldMap(): ?GuiFieldMap;
}
