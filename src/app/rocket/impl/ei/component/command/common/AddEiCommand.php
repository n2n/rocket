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
namespace rocket\impl\ei\component\command\common;

use n2n\l10n\DynamicTextCollection;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\l10n\N2nLocale;
use rocket\impl\ei\component\command\common\controller\AddController;
use rocket\spec\ei\component\command\control\OverallControlComponent;
use rocket\spec\ei\manage\control\ControlButton;
use rocket\spec\ei\manage\control\IconType;
use rocket\impl\ei\component\command\IndependentEiCommandAdapter;
use rocket\spec\ei\component\command\PrivilegedEiCommand;
use n2n\core\container\N2nContext;
use rocket\spec\security\impl\CommonEiCommandPrivilege;
use rocket\core\model\Rocket;
use n2n\l10n\Lstr;
use rocket\spec\ei\manage\control\EntryControlComponent;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\security\EiCommandPrivilege;
use n2n\web\http\controller\Controller;

class AddEiCommand extends IndependentEiCommandAdapter implements OverallControlComponent, EntryControlComponent,
		PrivilegedEiCommand {
	const ID_BASE = 'add';
	const CONTROL_ADD_KEY = 'add';
	const CONTROL_DUPLICATE_KEY = 'duplicate';
	const CONTROL_ADD_DRAFT_KEY = 'addDraft';
	const CONTROL_DUPLICATE_DRAFT_KEY = 'duplicateDraft';
	const CONTROL_ADD_ROOT_BRANCH_KEY = 'addRootBranch';
	const CONTROL_INSERT_BRANCH_KEY = 'insertBranch';

	const PRIVILEGE_LIVE_ENTRY_KEY = 'eiEntityObj';
	const PRIVILEGE_DRAFT_KEY = 'draft';

	private $dublicatingAllowed = true;

	public function getIdBase() {
		return self::ID_BASE;
	}

	public function isDublicatingAllowed() {
		return $this->dublicatingAllowed;
	}

	public function setDublicatingAllowed(bool $dublicatingAllowed) {
		$this->dublicatingAllowed = $dublicatingAllowed;
	}

	public function getPrivilegeLabel(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return $dtc->translate('common_new_entry_label');
	}

	public function createEiCommandPrivilege(N2nContext $n2nContext): EiCommandPrivilege {
		$pi = new CommonEiCommandPrivilege(new Lstr('common_new_entry_label', Rocket::NS));
		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_LIVE_ENTRY_KEY,
				new CommonEiCommandPrivilege(new Lstr('ei_impl_add_live_entry_label', Rocket::NS)));
		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_DRAFT_KEY,
				new CommonEiCommandPrivilege(new Lstr('ei_impl_add_draft_label', Rocket::NS)));
		return $pi;
	}

	public function lookupController(Eiu $eiu): Controller {
		return $eiu->lookup(AddController::class);
	}

	public function getOverallControlOptions(N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);

		$options = array();
		
		if (null === $this->eiEngine->getEiType()->getNestedSetStrategy()) {
			$options[self::CONTROL_ADD_KEY] = $dtc->t('common_new_entry_label');
		} else {
			$options[self::CONTROL_ADD_ROOT_BRANCH_KEY] = $dtc->t('ei_impl_add_root_branch_label');
		}

		$options[self::CONTROL_ADD_DRAFT_KEY] = $dtc->translate('common_add_draft_label');
		
		return $options;
	}

	public function createOverallControls(Eiu $eiu, HtmlView $view): array {
		$eiuControlFactory = $eiu->frame()->controlFactory($this);
		$dtc = $eiu->dtc('rocket');
		
		$nestedSet = null !== $this->eiEngine->getEiType()->getNestedSetStrategy();
		
		$controls = array();
		
		$key = $nestedSet ? self::CONTROL_ADD_ROOT_BRANCH_KEY : self::CONTROL_ADD_KEY;
		$controls[$key] = $eiuControlFactory->createJhtml(new ControlButton(
				$dtc->t($nestedSet ? 'ei_impl_add_root_branch_label' : 'common_new_entry_label'),
				null, true, ControlButton::TYPE_SUCCESS, IconType::ICON_PLUS_CIRCLE));
		
		if ($eiu->frame()->isDraftingEnabled()) {
			$controls[self::CONTROL_ADD_DRAFT_KEY] = $eiuControlFactory->createJhtml(new ControlButton(
					$dtc->translate('ei_impl_add_draft_label'),
					null, true, ControlButton::TYPE_SUCCESS, IconType::ICON_PLUS_CIRCLE));
		}
		
		return $controls;
	}

	public function getEntryControlOptions(N2nContext $n2nContext, N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		
		return array(self::CONTROL_INSERT_BRANCH_KEY => $dtc->t('ei_impl_insert_branch_label'));
	}

	public function createEntryControls(Eiu $eiu, HtmlView $view): array {
		if ($eiu->frame()->isExecutedBy($this)) {
			return array();
		}
		
		$eiuControlFactory = $eiu->frame()->controlFactory($this);
		$eiuEntry = $eiu->entry();
		$dtc = $eiu->dtc('rocket');
		
		if ($eiu->frame()->getNestedSetStrategy() === null) {
			if (!$this->dublicatingAllowed) return array();
			
			$name = $dtc->t('ei_impl_duplicate_label');
			$tooltip = $dtc->t('ei_impl_duplicate_tooltip', array('entry' => $eiuEntry->createIdentityString()));
			$controlButton = new ControlButton($name, $tooltip, false, ControlButton::TYPE_SUCCESS, IconType::ICON_COPY);
			
			return array(self::CONTROL_DUPLICATE_KEY => $eiuControlFactory
					->createJhtml($controlButton, [$eiuEntry->getLivePid()]));
		}


		$groupControl = $eiuControlFactory->createGroup(new ControlButton($dtc->t('ei_impl_insert_branch_label'),
						$dtc->t('ei_impl_add_branch_tooltip'), false, ControlButton::TYPE_SECONDARY, 
						IconType::ICON_PLUS));
		
		$groupControl->add(
				 $eiuControlFactory->createJhtml(
						new ControlButton($dtc->t('ei_impl_add_before_branch_label'),
								$dtc->t('ei_impl_add_before_branch_tooltip'),
								true, ControlButton::TYPE_SUCCESS, IconType::ICON_ANGLE_UP),
						['before', $eiuEntry->getLivePid()]),
				$eiuControlFactory->createJhtml(
						new ControlButton($dtc->t('ei_impl_add_after_branch_label'),
								$dtc->t('ei_impl_add_after_branch_tooltip'),
								true, ControlButton::TYPE_SUCCESS, IconType::ICON_ANGLE_DOWN),
						['after', $eiuEntry->getLivePid()]),
				$eiuControlFactory->createJhtml(
						new ControlButton($dtc->translate('ei_impl_add_child_branch_label'),
								$dtc->translate('ei_impl_add_child_branch_tooltip'),
								true, ControlButton::TYPE_SUCCESS, IconType::ICON_ANGLE_RIGHT),
						['child', $eiuEntry->getLivePid()]));
		
		return array(self::CONTROL_INSERT_BRANCH_KEY => $groupControl);
	}
}
