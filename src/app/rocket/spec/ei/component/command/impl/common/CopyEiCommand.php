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
namespace rocket\spec\ei\component\command\impl\common;

use rocket\spec\ei\manage\EiState;
use n2n\l10n\DynamicTextCollection;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\control\EntryControlComponent;
use rocket\spec\ei\manage\control\ControlButton;
use rocket\spec\ei\component\command\impl\common\controller\PathUtils;
use rocket\spec\ei\manage\control\IconType;
use rocket\spec\ei\component\command\impl\common\controller\CopyController;
use rocket\spec\ei\component\command\impl\IndependentEiCommandAdapter;
use rocket\spec\ei\manage\model\EntryModel;
use rocket\spec\ei\manage\model\EntryGuiModel;

class CopyEiCommand extends IndependentEiCommandAdapter implements EntryControlComponent {
	const ID_BASE = 'rocket-copy';
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName(): string {
		return 'Copy (Rocket)';
	}
	
	public function lookupController(EiState $eiState) {
		$copyController = new CopyController($eiState);
		$copyController->set($this->getEiSpec());
		return $copyController;
	}
	
	public function createEntryHrefControls(EiuGui $entryGuiUtils, HtmlView $view): array {
		
		$request = $view->getRequest();
		$dtc = new DynamicTextCollection('rocket', $request->getN2nLocale());
		
		$url = $request->getControllerContextPath($eiState->getControllerContext(), 
						PathUtils::createDetailPathExtFromEiSelection(self::ID_BASE, 
								$entryGuiModel->getEiMapping()->getEiSelection(), 
								$eiState->getPreviewType()));
		$name = $dtc->translate('ei_impl_copy_label');
		$tooltip = $dtc->translate('ei_impl_copy_tooltip');
		
		return array(self::ID_BASE => new ControlButton($url, $name, $tooltip, true, ControlButton::TYPE_SUCCESS,
				IconType::ICON_COPY));
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\control\EntryControlComponent::getEntryControlOptions()
	 */
	public function getEntryControlOptions(\n2n\l10n\N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $request->getN2nLocale());
		return array(self::ID_BASE => $dtc->translate('ei_impl_copy_label'));
	}

}
