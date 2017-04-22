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

use rocket\spec\ei\manage\EiFrame;
use n2n\l10n\DynamicTextCollection;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\control\EntryControlComponent;
use rocket\spec\ei\manage\control\ControlButton;
use rocket\spec\ei\manage\control\IconType;
use rocket\spec\ei\component\command\impl\common\controller\EditController;
use rocket\spec\ei\component\command\impl\IndependentEiCommandAdapter;
use rocket\spec\ei\component\command\PrivilegedEiCommand;
use n2n\core\container\N2nContext;
use rocket\spec\security\EiCommandPrivilege;
use rocket\spec\security\impl\CommonEiCommandPrivilege;
use rocket\core\model\Rocket;
use n2n\l10n\Lstr;
use rocket\spec\ei\EiCommandPath;
use rocket\spec\ei\manage\control\HrefControl;
use n2n\util\uri\Path;
use rocket\spec\ei\manage\util\model\Eiu;
use n2n\web\http\controller\Controller;

class EditEiCommand extends IndependentEiCommandAdapter implements EntryControlComponent, PrivilegedEiCommand {
	const ID_BASE = 'edit';
	const CONTROL_KEY = 'edit';
	const PRIVILEGE_LIVE_ENTRY_KEY = 'eiEntityObj';
	const PRIVILEGE_DRAFT_KEY = 'draft';
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName(): string {
		return 'Edit';
	}
	
	public function lookupController(Eiu $eiu): Controller {
		return $eiu->lookup(EditController::class);
	}
	
	public function getEntryControlOptions(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_KEY => $dtc->translate('common_edit_label'));
	}
	
	public function createEntryControls(Eiu $eiu, HtmlView $view): array {
		if ($eiu->frame()->isExecutedBy($this)) {
			return array();
		}

		$eiuControlFactory = $eiu->frame()->controlFactory($view);
		$eiuEntry = $eiu->entry();
		$eiuFrame = $eiu->frame();
		$dtc = new DynamicTextCollection('rocket', $view->getN2nLocale());
		
		$controls = array();

		if (!$eiuEntry->isDraft()) {
			$controlButton = new ControlButton($dtc->t('common_edit_label'), 
					$dtc->t('ei_impl_edit_entry_tooltip', array('entry' => $eiuFrame->getGenericLabel())), 
					true, ControlButton::TYPE_WARNING, IconType::ICON_PENCIL);
			$urlExt = (new Path(array('live', $eiuEntry->getLiveIdRep())))
					->toUrl(array('refPath' => (string) $eiuFrame->getCurrentUrl()));
			
			$controls[] = $eiuControlFactory->createAjah($this, $controlButton, $urlExt);
			
			if ($eiuFrame->isDraftingEnabled()) {
				$controlButton = new ControlButton($dtc->t('common_edit_latest_draft_label'),
						$dtc->t('ei_impl_edit_latest_draft_tooltip', array('entry' => $eiuFrame->getGenericLabel())),
						true, ControlButton::TYPE_PRIMARY, IconType::ICON_PENCIL_SQUARE);
				$urlExt = (new Path(array('latestdraft', $eiuEntry->getLiveIdRep())))
						->toUrl(array('refPath' => (string) $eiuFrame->getCurrentUrl()));
				
				$controls[] = $eiuControlFactory->createAjah($this, $controlButton, $urlExt);
			}
		} else if (!$eiuEntry->isDraftNew()) {
			$controlButton = new ControlButton($dtc->t('ei_impl_edit_draft_label'), $dtc->t('ei_impl_edit_draft_tooltip'), 
					true, ControlButton::TYPE_WARNING, IconType::ICON_PENCIL_SQUARE);
			$urlExt = (new Path(array('draft', $eiuEntry->getDraftId())))
					->toUrl(array('refPath' => $eiuFrame->getCurrentUrl()));
			$controls[] = $eiuControlFactory->createAjah($this, $controlButton, $urlExt);
			
			$controlButton = new ControlButton($dtc->t('ei_impl_publish_draft_label'), 
					$dtc->t('ei_impl_publish_draft_tooltip'), true, ControlButton::TYPE_WARNING, IconType::ICON_CHECK_SQUARE);
			$urlExt = (new Path(array('publish', $eiuEntry->getDraftId())))
					->toUrl(array('refPath' => (string) $eiuFrame->getCurrentUrl($view->getHttpContext())));
			$controls[] = $eiuControlFactory->createAjah($this, $controlButton, $urlExt);
		}
		
		return $controls;	
	}
	
	public function createEiCommandPrivilege(N2nContext $n2nContext): EiCommandPrivilege {
		$pi = new CommonEiCommandPrivilege(new Lstr('common_edit_label', Rocket::NS));
		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_LIVE_ENTRY_KEY,
				new CommonEiCommandPrivilege(new Lstr('ei_impl_edit_live_entry_label', Rocket::NS)));
		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_DRAFT_KEY,
				new CommonEiCommandPrivilege(new Lstr('ei_impl_edit_draft_label', Rocket::NS)));
		return $pi;
	}
	
	
	public function getPrivilegeLabel(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return $dtc->translate('common_edit_label'); 
	}
	
	public function getPrivilegeExtOptions(N2nLocale $n2nLocale) {
		if (!$this->getEiSpec()->isDraftable()) return array();
		
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::PRIVILEGE_EXT_PUBLISH => $dtc->translate('ei_impl_edit_privilege_publish_label'));
	}
}
