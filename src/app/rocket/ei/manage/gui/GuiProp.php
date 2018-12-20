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
namespace rocket\ei\manage\gui;

use n2n\l10n\N2nLocale;
use rocket\ei\util\Eiu;
use n2n\l10n\Lstr;

interface GuiProp {
	/**
	 * @return Lstr 
	 */
	public function getDisplayLabelLstr(): Lstr;
	
	/**
	 * @return Lstr|NULL
	 */
	public function getDisplayHelpTextLstr(): ?Lstr;
	
	/**
	 * <p>Tests if this GuiProp is compatible with the passed EiGui and returns an {@see DisplayDefinition}
	 * if it does. Use <code>$eiu->gui()</code> to access the {@see \rocket\ei\util\gui\EiuGui} 
	 * object.<p>
	 * 
	 * @return DisplayDefinition|null return null if this GuiProp is not compatible with passed EiGui.
	 */
	public function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition;
		
	/**
	 * @param Eiu $eiu
	 * @return \rocket\ei\manage\gui\GuiField|null
	 */
	public function buildGuiField(Eiu $eiu): ?GuiField;
		
	/**
	 * @return boolean
	 */
	public function isStringRepresentable(): bool;
	
	/**
	 * @param object $entity
	 * @return string|null
	 */
	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string;
}