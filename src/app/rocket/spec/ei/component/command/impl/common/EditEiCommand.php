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
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\util\uri\Path;
use rocket\spec\ei\manage\util\model\EntryGuiUtils;

class EditEiCommand extends IndependentEiCommandAdapter implements EntryControlComponent, PrivilegedEiCommand {
	const ID_BASE = 'edit';
	const CONTROL_KEY = 'edit';
	const PRIVILEGE_LIVE_ENTRY_KEY = 'liveEntry';
	const PRIVILEGE_DRAFT_KEY = 'draft';
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName(): string {
		return 'Edit';
	}
		
	public function lookupController(EiState $eiState) {
		$editController = new EditController();
		$eiState->getN2nContext()->magicInit($editController);
		return $editController;
	}
	
	public function getEntryControlOptions(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_KEY => $dtc->translate('common_edit_label'));
	}
	
	public function createEntryHrefControls(EntryGuiUtils $entryGuiUtils, HtmlView $view): array {
		$eiMapping = $entryGuiUtils->getEiMapping();
		
		$eiSelection = $eiMapping->getEiSelection();
		$eiState = $entryGuiUtils->getEiState();
		if ($eiState->getEiExecution()->getEiCommandPath()->startsWith(EiCommandPath::from($this))) {
			return array();
		}
		
		$eiUtils = $entryGuiUtils->getEiuFrame();
		
		$hrefControls = array();

		if (!$eiSelection->isDraft()) {
			$urlExt = (new Path(array('live', $eiUtils->idToIdRep($eiSelection->getLiveEntry()->getId()))))
					->toUrl(array('refPath' => (string) $eiState->getCurrentUrl($view->getHttpContext())));
			$label = $view->getL10nText('common_edit_label');
			$tooltip = $view->getL10nText('ei_impl_edit_entry_tooltip', array('entry' => $eiUtils->getGenericLabel()));
			$hrefControls[] = HrefControl::create($eiState, $this, $urlExt,
					new ControlButton($label, $tooltip, true, ControlButton::TYPE_WARNING, IconType::ICON_PENCIL));
			if ($eiUtils->isDraftingEnabled()) {
				$urlExt = (new Path(array('latestdraft', $eiUtils->idToIdRep($eiSelection->getLiveEntry()->getId()))))
						->toUrl(array('refPath' => (string) $eiState->getCurrentUrl($view->getHttpContext())));
				$label = $view->getL10nText('common_edit_latest_draft_label');
				$tooltip = $view->getL10nText('ei_impl_edit_latest_draft_tooltip', array('entry' => $eiUtils->getGenericLabel()));
				$hrefControls[] = HrefControl::create($eiState, $this, $urlExt,
						new ControlButton($label, $tooltip, true, ControlButton::TYPE_WARNING, IconType::ICON_PENCIL_SQUARE));
			}
		} else if (!$eiSelection->getDraft()->isNew()) {
			$urlExt = (new Path(array('draft', $eiSelection->getDraft()->getId())))
					->toUrl(array('refPath' => (string) $eiState->getCurrentUrl($view->getHttpContext())));
			$label = $view->getL10nText('ei_impl_edit_draft_label');
			$tooltip = $view->getL10nText('ei_impl_edit_draft_tooltip');
			$hrefControls[] = HrefControl::create($eiState, $this, $urlExt,
					new ControlButton($label, $tooltip, true, ControlButton::TYPE_WARNING, IconType::ICON_PENCIL_SQUARE));
			
			$urlExt = (new Path(array('publish', $eiSelection->getDraft()->getId())))
					->toUrl(array('refPath' => (string) $eiState->getCurrentUrl($view->getHttpContext())));
			$label = $view->getL10nText('ei_impl_publish_draft_label');
			$tooltip = $view->getL10nText('ei_impl_publish_draft_tooltip');
			$hrefControls[] = HrefControl::create($eiState, $this, $urlExt,
					new ControlButton($label, $tooltip, true, ControlButton::TYPE_WARNING, IconType::ICON_CHECK_SQUARE));
		}
		
		return $hrefControls;
		
// 		if ($eiUtils->getEiMask()->isDraftingEnabled()) {
// 			$label = $view->getL10nText('ei_impl_edit_latest_draft_label');
// 			$tooltip = $view->getL10nText('ei_impl_edit_latest_draft_tooltip');
// 			$pathExt = new Path('draft');
// 		}
		
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
