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

use rocket\spec\ei\manage\control\EntryNavPoint;
use rocket\spec\ei\manage\EiState;
use n2n\l10n\DynamicTextCollection;
use n2n\web\ui\view\impl\html\HtmlView;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\control\EntryControlComponent;
use rocket\spec\ei\component\command\impl\common\controller\DetailController;
use rocket\spec\ei\manage\control\ControlButton;
use rocket\spec\ei\component\command\impl\common\controller\PathUtils;
use rocket\spec\ei\manage\control\IconType;
use rocket\spec\ei\component\command\impl\IndependentEiCommandAdapter;
use rocket\spec\ei\component\command\PrivilegedEiCommand;
use n2n\util\uri\Path;
use rocket\spec\ei\manage\model\EntryGuiModel;
use n2n\core\container\N2nContext;
use rocket\spec\security\impl\CommonEiCommandPrivilege;
use rocket\core\model\Rocket;
use rocket\spec\security\EiCommandPrivilege;
use n2n\l10n\Lstr;
use rocket\spec\ei\EiCommandPath;
use rocket\spec\ei\manage\control\HrefControl;
use rocket\spec\ei\component\command\GenericDetailEiCommand;
use rocket\spec\ei\manage\util\model\EiStateUtils;

class DetailEiCommand extends IndependentEiCommandAdapter implements EntryControlComponent, GenericDetailEiCommand, 
		PrivilegedEiCommand {
	const ID_BASE = 'detail';
	const CONTROL_DETAIL_KEY = 'detail'; 
	const CONTROL_PREVIEW_KEY = 'preview';
		
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName(): string {
		return 'Detail';
	}
		
	public function lookupController(EiState $eiState) {
		$detailController = new DetailController();
		$eiState->getN2nContext()->magicInit($detailController);
		return $detailController;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\control\EntryControlComponent::getEntryControlOptions()
	 */
	public function getEntryControlOptions(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_DETAIL_KEY => $dtc->translate('ei_impl_detail_label'), 
				self::CONTROL_PREVIEW_KEY => $dtc->translate('ei_impl_preview_label'));
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\control\EntryControlComponent::createEntryHrefControls()
	 */
	public function createEntryHrefControls(EntryGuiModel $entryGuiModel, EiState $eiState, HtmlView $view): array {
		$eiUtils = new EiStateUtils($eiState);
		
		$eiSelection = $entryGuiModel->getEiMapping()->getEiSelection();
		
		if ($eiState->getEiExecution()->getEiCommandPath()->startsWith(EiCommandPath::from($this))) {
			return array();
		}
		
		$pathExt = null;
		if (!$eiSelection->isDraft()) {
			$pathExt = new Path(array('live', $eiUtils->idToIdRep($eiSelection->getLiveEntry()->getId())));
		} else if (!$eiSelection->getDraft()->isNew()) {
			$pathExt = new Path(array('draft', $eiSelection->getDraft()->getId()));
		} else {
			return array();
		}
		
		$controlButton = new ControlButton(
				$view->getL10nText('ei_impl_detail_label'), $view->getL10nText('ei_impl_detail_tooltip',
						array('entry' => $eiState->getContextEiMask()->getLabelLstr()->t($view->getN2nLocale()))),
				true, ControlButton::TYPE_DEFAULT, IconType::ICON_FILE);
		
		$hrefControls = array(self::CONTROL_DETAIL_KEY 
				=> HrefControl::create($eiState, $this, $pathExt->toUrl(), $controlButton));
		
		$eiEntryUtils = $eiUtils->toEiEntryUtils($eiSelection);
		$previewType = $eiEntryUtils->getPreviewType();
		if ($previewType === null) {
			return $hrefControls;
		}
		
		if (!$eiSelection->isDraft()) {
			$pathExt = new Path(array('livepreview', $eiEntryUtils->getIdRep(), $previewType));
		} else {
			$pathExt = new Path(array('draftpreview', $eiEntryUtils->getDraft()->getId(), $previewType));
		}
		
		$controlButton = new ControlButton(
				$view->getL10nText('ei_impl_detail_preview_label'), $view->getL10nText('ei_impl_detail_preview_tooltip',
						array('entry' => $eiState->getContextEiMask()->getLabelLstr()->t($view->getN2nLocale()))),
				true, ControlButton::TYPE_DEFAULT, IconType::ICON_EYE);
		
		$hrefControls[self::CONTROL_PREVIEW_KEY] = HrefControl::create($eiState, $this, $pathExt->toUrl(), $controlButton);
		return $hrefControls;
	}
	
	public function getDetailUrlExt(EntryNavPoint $entryNavPoint) {
// 		if (!$this->getEiSpec()->getDefaultEiDef()->getisPreviewAvailable()) {
// 			$entryNavPoint = $entryNavPoint->copy(false, false, true);
// 		}
		
		return PathUtils::createPathExtFromEntryNavPoint($this, $entryNavPoint)->toUrl();
	}
	
	public function createEiCommandPrivilege(N2nContext $n2nContext): EiCommandPrivilege {
		return new CommonEiCommandPrivilege(new Lstr('ei_impl_detail_label', Rocket::NS));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\command\GenericDetailEiCommand::isDetailAvailable($entryNavPoint)
	 */
	public function isDetailAvailable(EntryNavPoint $entryNavPoint): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\command\GenericDetailEiCommand::buildDetailPathExt($entryNavPoint)
	 */
	public function getDetailPathExt(EntryNavPoint $entryNavPoint): Path {
		return PathUtils::createPathExtFromEntryNavPoint($this, $entryNavPoint);
	}
}
