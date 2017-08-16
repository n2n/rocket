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
namespace rocket\spec\ei\mask;

use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\EiType;
use rocket\spec\ei\manage\EiFrame;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\preview\model\PreviewModel;
use n2n\web\ui\view\View;
use rocket\spec\ei\EiThing;
use rocket\spec\ei\manage\preview\controller\PreviewController;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\manage\gui\EiGui;

interface EiMask extends EiThing {
		
	/**
	 * @param string $id
	 */
	public function setId(string $id = null);
	
	/**
	 * @param EiType $eiType
	 * @return EiMask
	 */
	public function determineEiMask(EiType $eiType): EiMask;
	
	/**
	 * @param EiGui $eiGui
	 * @param HtmlView $htmlView
	 * @return \rocket\spec\ei\component\command\ControlButton[]
	 */
	public function sortOverallControls(array $controls, EiGui $eiGui, HtmlView $htmlView): array;
	
	/**
	 * @param HtmlView $view
	 * @param EntryModel $entryModel
	 * @return \rocket\spec\ei\component\command\ControlButton[]
	 */
	public function sortEntryControls(array $controls, EiEntryGui $eiEntryGui, HtmlView $view): array;
		
	/**
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(EiObject $eiObject, N2nLocale $n2nLocale): string;
	
	// 	/**
	// 	 * @param EiFrame $eiFrame
	// 	 * @param EiEntry $eiEntry
	// 	 * @param string $viewMode
	// 	 * @param bool $makeEditable
	// 	 * @return \rocket\spec\ei\manage\gui\EiEntryGui
	// 	 */
	// 	public function createEiEntryGui(EiFrame $eiFrame, EiEntry $eiEntry, $viewMode, $makeEditable);
	
	/**
	 * @return boolean
	 */
	public function isDraftingEnabled();

	/**
	 * @param EiFrame $eiFrame
	 * @param int $allowedViewMods
	 * @return EiGui
	 */
	public function createEiGui(EiFrame $eiFrame, int $allowedViewMods): EiGui;

	/**
	 * @return bool
	 */
	public function isPreviewSupported(): bool;
	
	/**
	 * @param PreviewModel $previewModel
	 * @return PreviewController
	 */
	public function lookupPreviewController(EiFrame $eiFrame, PreviewModel $previewModel = null): PreviewController;

	public function __toString(): string;
}
