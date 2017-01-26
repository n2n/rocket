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
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\manage\EiState;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\mapping\EiMapping;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\config\mask\model\EntryGuiTree;
use rocket\spec\ei\manage\EntryGui;
use rocket\spec\ei\manage\preview\model\PreviewModel;
use rocket\spec\ei\manage\model\EntryGuiModel;
use n2n\web\ui\view\View;
use rocket\spec\ei\EiThing;
use rocket\spec\ei\manage\preview\controller\PreviewController;
use rocket\spec\ei\manage\util\model\EiuGui;

interface EiMask extends EiThing {
		
	/**
	 * @param string $id
	 */
	public function setId(string $id = null);
	
	/**
	 * @param EiSpec $eiSpec
	 * @return EiMask
	 */
	public function determineEiMask(EiSpec $eiSpec): EiMask;
	
	// 	/**
	// 	 * @param string $id
	// 	 */
	// 	public function getControlById(string $id): Control;
	
// 	/**
// 	 * @return boolean
// 	 */
// 	public function hasOverviewControl(): bool;
	
// 	/**
// 	 * @return \rocket\spec\ei\component\command\GenericOverviewEiCommand
// 	 */
// 	public function buildOverviewPathExt(): Path;
	
// 	/**
// 	 * @return boolean
// 	 */
// 	public function hasDetailControl(EntryNavPoint $entryNavPoint): bool;
	
// 	public function buildDetailPathExt(EntryNavPoint $entryNavPoint): Path;
// 	/**
// 	 * @return boolean
// 	 */
// 	public function hasEditControl(EntryNavPoint $entryNavPoint): bool;
	
// 	public function buildEditPathExt(EntryNavPoint $entryNavPoint): Path;
	
// 	public function hasAddControl(bool $draft): bool;
	
// 	public function buildAddPathExt(bool $draft): Path;
	
	
	/**
	 * @param EiState $eiState
	 * @param HtmlView $htmlView
	 * @return \rocket\spec\ei\component\command\ControlButton[]
	 */
	public function createOverallHrefControls(EiState $eiState, HtmlView $htmlView): array;
	
	/**
	 * @param HtmlView $view
	 * @param EntryModel $entryModel
	 * @return \rocket\spec\ei\component\command\ControlButton[]
	 */
	public function createEntryHrefControls(EiuGui $eiuGui, HtmlView $view): array;
		
	/**
	 * @param EiSelection $eiSelection
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(EiSelection $eiSelection, N2nLocale $n2nLocale): string;
	
	// 	/**
	// 	 * @param EiState $eiState
	// 	 * @param EiMapping $eiMapping
	// 	 * @param string $viewMode
	// 	 * @param bool $makeEditable
	// 	 * @return \rocket\spec\ei\manage\gui\EiSelectionGui
	// 	 */
	// 	public function createEiSelectionGui(EiState $eiState, EiMapping $eiMapping, $viewMode, $makeEditable);
	
	/**
	 * @return boolean
	 */
	public function isDraftingEnabled();

	public function createListEntryGuiModel(EiState $eiState, EiMapping $eiMapping,
			bool $makeEditable): EntryGuiModel;
	/**
	 * @param EiState $eiState;
	 * @param EntryListModel $entryListModel
	 * @return \n2n\web\ui\view\View
	 */
	public function createListView(EiState $eiState, array $entryGuis): View;

	public function createTreeEntryGuiModel(EiState $eiState, EiMapping $eiMapping,
			bool $makeEditable): EntryGuiModel;

	/**
	 * @param EiState $eiState
	 * @param EntryTreeListModel $entryListModel
	 * @return \n2n\web\ui\view\View
	 */
	public function createTreeView(EiState $eiState, EntryGuiTree $entryGuiTree): View;

	/**
	 * @param EiState $eiState
	 * @param EiMapping $eiMapping
	 * @param bool $makeEditable
	 * @return \rocket\spec\ei\manage\model\EntryGuiModel
	 */
	public function createBulkyEntryGuiModel(EiState $eiState, EiMapping $eiMapping,
			bool $makeEditable): EntryGuiModel;

	/**
	 * @param EiState $eiState
	 * @param EntryModel $entryModel
	 * @return \n2n\web\ui\view\View
	 */
	public function createBulkyView(EiState $eiState, EntryGui $entryGui): View;

	public function isPreviewSupported(): bool;
	
	/**
	 * @param PreviewModel $previewModel
	 * @return PreviewController
	 */
	public function lookupPreviewController(EiState $eiState, PreviewModel $previewModel = null): PreviewController;

	public function __toString(): string;
}
