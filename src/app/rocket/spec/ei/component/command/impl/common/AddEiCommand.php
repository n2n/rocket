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

use n2n\l10n\DynamicTextCollection;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\EiState;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\component\command\impl\common\controller\AddController;
use rocket\spec\ei\component\command\control\OverallControlComponent;
use rocket\spec\ei\manage\control\ControlButton;
use rocket\spec\ei\manage\control\IconType;
use rocket\spec\ei\component\command\impl\IndependentEiCommandAdapter;
use rocket\spec\ei\component\command\PrivilegedEiCommand;
use n2n\core\container\N2nContext;
use rocket\spec\security\impl\CommonEiCommandPrivilege;
use rocket\core\model\Rocket;
use n2n\l10n\Lstr;
use rocket\spec\ei\manage\control\HrefControl;
use rocket\spec\ei\manage\control\EntryControlComponent;
use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\security\EiCommandPrivilege;

class AddEiCommand extends IndependentEiCommandAdapter implements OverallControlComponent, EntryControlComponent, 
		PrivilegedEiCommand {
	const ID_BASE = 'add';
	const CONTROL_ADD_KEY = 'add';
	const CONTROL_ADD_DRAFT_KEY = 'addDraft';
	const CONTROL_ADD_CHILD_BRANCH_KEY = 'addChildBranch';
	const CONTROL_ADD_BEFORE_BRANCH_KEY = 'addBeforeBranch';
	const CONTROL_ADD_AFTER_BRANCH_KEY = 'addAfterBranch';
	const CONTROL_ADD_ROOT_BRANCH_KEY = 'addRootBranch';
	
	const PRIVILEGE_LIVE_ENTRY_KEY = 'liveEntry';
	const PRIVILEGE_DRAFT_KEY = 'draft';
	
	public function getIdBase() {
		return self::ID_BASE;
	}

	public function getPrivilegeLabel(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return $dtc->translate('common_add_label');
	}
	
	public function createEiCommandPrivilege(N2nContext $n2nContext): EiCommandPrivilege {
		$pi = new CommonEiCommandPrivilege(new Lstr('common_add_label', Rocket::NS));
		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_LIVE_ENTRY_KEY, 
				new CommonEiCommandPrivilege(new Lstr('ei_impl_add_live_entry_label', Rocket::NS)));
		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_DRAFT_KEY,
				new CommonEiCommandPrivilege(new Lstr('ei_impl_add_draft_label', Rocket::NS)));
		return $pi;
	}
	
	public function lookupController(EiState $eiState) {
		$addController = new AddController();
		$eiState->getN2nContext()->magicInit($addController);
		return $addController;
	}
	
	public function getOverallControlOptions(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		
		if (null !== $this->eiThing->getNestedSetStrategy()) {
			return array(self::CONTROL_ADD_ROOT_BRANCH_KEY => $dtc->translate('ei_impl_add_root_branch_label'),
					self::CONTROL_ADD_ROOT_BRANCH_DRAFT_KEY => $dtc->translate('ei_impl_add_root_branch_draft_label'));
		}
		
		return array(self::CONTROL_ADD_KEY => $dtc->translate('common_add_label'),
				self::CONTROL_ADD_DRAFT_KEY => $dtc->translate('common_add_draft_label'));
	}
	
	public function createOverallHrefControls(EiState $eiState, HtmlView $htmlView) {
		$n2nContext = $eiState->getN2nContext();
		$eiUtils = new EiuFrame($eiState);
		$httpContext = $n2nContext->getHttpContext();
		$dtc = new DynamicTextCollection('rocket', $n2nContext->getN2nLocale());
		$controllerContextPath = $httpContext->getControllerContextPath($eiState->getControllerContext());
		
		$nestedSet = null !== $this->eiEngine->getEiSpec()->getNestedSetStrategy();
		
		$path = $controllerContextPath->ext($this->getId());
		$name = $dtc->translate($nestedSet ? 'ei_impl_add_root_branch_label' : 'common_add_label');
		$tooltip = $dtc->translate($nestedSet ? 'ei_impl_add_root_branch_tooltip' : 'ei_impl_add_tooltip', 
				array('type' => $eiUtils->getGenericLabel()));
		
		$controlButtons = array(self::CONTROL_ADD_KEY => new HrefControl($path, new ControlButton($name, $tooltip, true, 
				ControlButton::TYPE_SUCCESS, IconType::ICON_PLUS_CIRCLE)));
		
		if ($eiState->getContextEiMask()->isDraftingEnabled()) {
			$path = $controllerContextPath->ext($this->getId(), 'draft');
			$name = $dtc->translate('ei_impl_add_draft_label');
			$tooltip = $dtc->translate('ei_impl_add_draft_tooltip', array('type' => $eiUtils->getGenericLabel()));
			
			$controlButtons[self::CONTROL_ADD_DRAFT_KEY] = new HrefControl($path, new ControlButton($name, $tooltip, true, 
					ControlButton::TYPE_SUCCESS, IconType::ICON_PLUS_SQUARE));
		}
		return $controlButtons;
	}
	
	public function getEntryControlOptions(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_ADD_CHILD_BRANCH_KEY => $dtc->translate('ei_impl_add_child_branch_label'),
				self::CONTROL_ADD_SIBLING_BRANCH_KEY => $dtc->translate('ei_impl_add_sibling_branch_label'));
	}
	
	public function createEntryHrefControls(Eiu $eiu, HtmlView $view): array {
		$nestedSetStrategy = $this->eiEngine->getEiSpec()->getNestedSetStrategy();
		if ($nestedSetStrategy === null) {
			return array();
		}
		
		$dtc = new DynamicTextCollection('rocket', $view->getRequest()->getN2nLocale());
		$httpContext = $view->getHttpContext();
		$eiState = $eiu->frame()->getEiState();
		$eiuEntry = $eiu->entry();
		
		return array(
				self::CONTROL_ADD_BEFORE_BRANCH_KEY => new HrefControl(
						$httpContext->getControllerContextPath($eiState->getControllerContext())
								->ext($this->getId(), 'before', $eiuEntry->getLiveIdRep()), 
						new ControlButton($dtc->translate('ei_impl_add_before_branch_label'), 
								$dtc->translate('ei_impl_add_before_branch_tooltip'),
								true, ControlButton::TYPE_SUCCESS, IconType::ICON_ANGLE_UP)),
				self::CONTROL_ADD_AFTER_BRANCH_KEY => new HrefControl(
						$httpContext->getControllerContextPath($eiState->getControllerContext())
								->ext($this->getId(), 'after', $eiuEntry->getLiveIdRep()),
						new ControlButton($dtc->translate('ei_impl_add_after_branch_label'),
								$dtc->translate('ei_impl_add_after_branch_tooltip'),
								true, ControlButton::TYPE_SUCCESS, IconType::ICON_ANGLE_DOWN)),
				self::CONTROL_ADD_CHILD_BRANCH_KEY => new HrefControl(
						$httpContext->getControllerContextPath($eiState->getControllerContext())
						->ext($this->getId(), 'child', $eiuEntry->getLiveIdRep()),
						new ControlButton($dtc->translate('ei_impl_add_child_branch_label'),
								$dtc->translate('ei_impl_add_child_branch_tooltip'),
								true, ControlButton::TYPE_SUCCESS, IconType::ICON_ANGLE_RIGHT)));
	}
}
