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
use n2n\l10n\DynamicTextCollection;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\control\EntryControlComponent;
use rocket\spec\ei\component\command\impl\common\controller\DetailController;
use rocket\spec\ei\manage\control\ControlButton;
use rocket\spec\ei\component\command\impl\common\controller\PathUtils;
use rocket\spec\ei\manage\control\IconType;
use rocket\spec\ei\component\command\impl\IndependentEiCommandAdapter;
use rocket\spec\ei\component\command\PrivilegedEiCommand;
use n2n\util\uri\Path;
use n2n\core\container\N2nContext;
use rocket\spec\security\impl\CommonEiCommandPrivilege;
use rocket\core\model\Rocket;
use rocket\spec\security\EiCommandPrivilege;
use n2n\l10n\Lstr;
use rocket\spec\ei\component\command\GenericDetailEiCommand;
use rocket\spec\ei\manage\util\model\Eiu;
use n2n\web\http\controller\Controller;

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
	
	public function lookupController(Eiu $eiu): Controller {
		return $eiu->lookup(DetailController::class);
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\control\EntryControlComponent::getEntryControlOptions()
	 */
	public function getEntryControlOptions(N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_DETAIL_KEY => $dtc->translate('ei_impl_detail_label'), 
				self::CONTROL_PREVIEW_KEY => $dtc->translate('ei_impl_preview_label'));
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\control\EntryControlComponent::createEntryControls()
	 */
	public function createEntryControls(Eiu $eiu, HtmlView $view): array {
		$eiuFrame = $eiu->frame();
		if ($eiuFrame->isExecutedBy($this)) {
			return array();
		}
		
		$eiuEntry = $eiu->entry();
		
		$pathExt = null;
		$iconType = null;
		if (!$eiuEntry->isDraft()) {
			$pathExt = new Path(array('live', $eiuEntry->getLiveIdRep()));
			$iconType = IconType::ICON_FILE;
		} else if (!$eiuEntry->isDraftNew()) {
			$pathExt = new Path(array('draft', $eiuEntry->getDraftId()));
			$iconType = IconType::ICON_FILE_O;
		} else {
			return array();
		}
		
		$dtc = $eiu->dtc('rocket');
		$eiuControlFactory = $eiu->frame()->controlFactory($view);
		
		$controlButton = new ControlButton(
				$dtc->t('ei_impl_detail_label'),
				$dtc->t('ei_impl_detail_tooltip', array('entry' => $eiuFrame->getGenericLabel())),
				false, null, $iconType);
		
		$controls = array(
				self::CONTROL_DETAIL_KEY => $eiuControlFactory->createAjah($this, $controlButton, $pathExt->toUrl()));
		
		
		$previewType = $eiuEntry->getPreviewType();
		if ($previewType === null) {
			return $controls;
		}
		
		if (!$eiuEntry->isDraft()) {
			$pathExt = new Path(array('livepreview', $eiuEntry->getLiveIdRep(), $previewType));
		} else {
			$pathExt = new Path(array('draftpreview', $eiuEntry->getDraftId(), $previewType));
		}
		
		$controlButton = new ControlButton(
				$dtc->t('ei_impl_detail_preview_label'), 
				$dtc->t('ei_impl_detail_preview_tooltip', array('entry' => $eiuFrame->getGenericLabel())),
				IconType::ICON_EYE);
		
		$controls[self::CONTROL_PREVIEW_KEY] = $eiuControlFactory->createAjah($this, $controlButton, $pathExt->toUrl());
		
		return $controls;
	}
	
	public function getDetailUrlExt(EntryNavPoint $entryNavPoint) {
// 		if (!$this->getEiType()->getDefaultEiDef()->getisPreviewAvailable()) {
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
