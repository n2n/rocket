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
namespace rocket\impl\ei\component\cmd\common;

use n2n\l10n\DynamicTextCollection;
use n2n\l10n\N2nLocale;
use rocket\ui\si\control\SiButton;
use rocket\ui\si\control\SiIconType;
use rocket\op\ei\component\command\PrivilegedEiCommand;
use n2n\core\container\N2nContext;
use rocket\core\model\Rocket;
use rocket\op\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use rocket\impl\ei\component\cmd\common\controller\AddController;
use rocket\impl\ei\component\cmd\adapter\EiCmdNatureAdapter;
use rocket\ui\si\control\SiNavPoint;
use rocket\ui\gui\control\GuiControl;

class AddEiCmdNature extends EiCmdNatureAdapter implements PrivilegedEiCommand {
	const ID_BASE = 'add';
	const CONTROL_ADD_KEY = 'add';
	const CONTROL_DUPLICATE_KEY = 'duplicate';
	const CONTROL_ADD_ROOT_BRANCH_KEY = 'addRootBranch';
	const CONTROL_INSERT_BRANCH_KEY = 'insertBranch';
	const CONTROL_INSERT_BEFORE_KEY = 'insertBefore';
	const CONTROL_INSERT_AFTER_KEY = 'insertAfter';
	const CONTROL_INSERT_CHILD_KEY = 'insertChild';
	const CONTROL_SAVE_KEY = 'save';

	const PRIVILEGE_LIVE_ENTRY_KEY = 'eiEntityObj';
	const PRIVILEGE_DRAFT_KEY = 'draft';

	private bool $duplicatingAllowed = true;
	private ?string $controlLabel = null;
	
	protected function prepare() {
	}

	public function getIdBase(): ?string {
		return self::ID_BASE;
	}

	public function isDuplicatingAllowed(): bool {
		return $this->duplicatingAllowed;
	}

	/**
	 * @param bool $duplicatingAllowed
	 * @return $this
	 */
	public function setDuplicatingAllowed(bool $duplicatingAllowed): static {
		$this->duplicatingAllowed = $duplicatingAllowed;
		return $this;
	}

	public function getPrivilegeLabel(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return $dtc->translate('common_new_entry_label');
	}

	function setControlLabel(?string $controlLabel): static {
		$this->controlLabel = $controlLabel;
		return $this;
	}

	function getControlLabel(): ?string {
		return $this->controlLabel;
	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\op\ei\component\cmd\PrivilegedEiCommand::createEiCommandPrivilege()
// 	 */
// 	public function createEiCommandPrivilege(Eiu $eiu): EiCommandPrivilege {
// 		$dtc = $eiu->dtc(Rocket::NS);
// 		$eiuCommandPrivilege = $eiu->factory()->newCommandPrivilege($dtc->t('common_new_entry_label'));
		
// 		$eiuCommandPrivilege->newSub(self::PRIVILEGE_LIVE_ENTRY_KEY, $dtc->t('ei_impl_add_live_entry_label'));
// 		$eiuCommandPrivilege->newSub(self::PRIVILEGE_DRAFT_KEY, $dtc->t('ei_impl_add_draft_label'));
		
// 		return $eiuCommandPrivilege;
		
// // 		$pi = new CommonEiCommandPrivilege(Rocket::createLstr('common_new_entry_label', Rocket::NS));
// // 		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_LIVE_ENTRY_KEY,
// // 				new CommonEiCommandPrivilege(Rocket::createLstr('ei_impl_add_live_entry_label', Rocket::NS)));
// // 		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_DRAFT_KEY,
// // 				new CommonEiCommandPrivilege(Rocket::createLstr('ei_impl_add_draft_label', Rocket::NS)));
// // 		return $pi;
// 	}

	public function lookupController(Eiu $eiu): Controller {
		return $eiu->lookup(AddController::class);
	}

// 	public function getOverallControlOptions(N2nLocale $n2nLocale): array {
// 		$dtc = new DynamicTextCollection('rocket', $n2nLocale);

// 		$options = array();
		
// 		if (null === $this->eiMask->getEiType()->getNestedSetStrategy()) {
// 			$options[self::CONTROL_ADD_KEY] = $dtc->t('common_new_entry_label');
// 		} else {
// 			$options[self::CONTROL_ADD_ROOT_BRANCH_KEY] = $dtc->t('ei_impl_add_root_branch_label');
// 		}

// 		$options[self::CONTROL_ADD_DRAFT_KEY] = $dtc->translate('common_add_draft_label');
		
// 		return $options;
// 	}
	
	public function createGeneralGuiControls(Eiu $eiu): array {
		if ($eiu->frame()->isExecutedBy($eiu->cmd()) || $eiu->guiDefinition()->isBulky()) {
			return [];
		}
		
		return $this->createAddControls($eiu);
	}
	

	private function createAddControls(Eiu $eiu): array {
		$eiuControlFactory = $eiu->factory()->guiControl();
		$dtc = $eiu->dtc(Rocket::NS);
		
		$nestedSet = null !== $eiu->cmd()->getEiCmd()->getEiCommandCollection()->getEiMask()->getEiType()->getNestedSetStrategy();
		
		$key = $nestedSet ? self::CONTROL_ADD_ROOT_BRANCH_KEY : self::CONTROL_ADD_KEY;
		$label = $this->controlLabel ?? $dtc->t($nestedSet ? 'ei_impl_add_root_branch_label' : 'common_new_entry_label');
		$siButton = SiButton::success($label, SiIconType::ICON_PLUS_CIRCLE)
				->setImportant(true);
		return [$key => $eiuControlFactory->newCmdRef($siButton)];
	}
	
// 	/**
// 	 * @param Eiu $eiu
// 	 * @return \rocket\op\ei\util\control\EiuCallbackGuiControl
// 	 */
// 	private function createSaveControl(Eiu $eiu) {
// 		$eiuControlFactory = $eiu->factory()->controls();
// 		$dtc = $eiu->dtc(Rocket::NS);
		
// 		$siButton = SiButton::primary($dtc->t('common_save_label'), SiIconType::ICON_SAVE);
// 		$callback = function (Eiu $eiu, $inputEius) {
// 			$eiuResponse = $eiu->factory()->newControlResponse()->redirectBack();
			
// 			foreach ($inputEius as $inputEiu) {
// 				$inputEiu->entry()->save();
// 				$eiuResponse->highlight($inputEiu->entry());
// 			}
			
// 			return $eiuResponse; 
// 		};
		
// 		return $eiuControlFactory->newCallback(self::CONTROL_SAVE_KEY, $siButton, $callback)->setInputHandled(true);
// 	}

	public function getEntryGuiControlOptions(N2nContext $n2nContext, N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		
		return array(self::CONTROL_INSERT_BRANCH_KEY => $dtc->t('ei_impl_insert_branch_label'));
	}

	public function createEntryGuiControls(Eiu $eiu): array {
		$eiuEntry = $eiu->entry();
		
		if ($eiuEntry->isNew() || $eiu->frame()->isExecutedBy($eiu->cmd())) {
			return array();
		}
		
		$eiuControlFactory = $eiu->factory()->guiControl();
		$dtc = $eiu->dtc(Rocket::NS);
		
		if ($eiu->frame()->getNestedSetStrategy() === null) {
			if (!$this->duplicatingAllowed) return array();
			
			$name = $dtc->t('ei_impl_duplicate_label');
			$tooltip = $dtc->t('ei_impl_duplicate_tooltip', array('entry' => $eiuEntry->createIdentityString()));
			$siButton = SiButton::success($name,SiIconType::ICON_R_COPY)->setTooltip($tooltip);
			
			return array(self::CONTROL_DUPLICATE_KEY => $eiuControlFactory
					->newCmdRef($siButton, [$eiuEntry->getPid()]));
		}

		$groupControl = $eiuControlFactory->newGroup(
				SiButton::success($dtc->t('ei_impl_insert_branch_label'), SiIconType::ICON_PLUS)
						->setTooltip($dtc->t('ei_impl_add_branch_tooltip'))
						->setImportant(false));
		
		$groupControl->putGuiControl(self::CONTROL_INSERT_BEFORE_KEY,
				$eiuControlFactory->newCmdRef(
						SiButton::success($dtc->t('ei_impl_add_before_branch_label'), SiIconType::ICON_ANGLE_UP)
								->setTooltip($dtc->t('ei_impl_add_before_branch_tooltip'))
								->setImportant(TRUE),
						['before', $eiuEntry->getPid()]));
		$groupControl->putGuiControl(self::CONTROL_INSERT_AFTER_KEY,
				$eiuControlFactory->newCmdRef(
						SiButton::success($dtc->t('ei_impl_add_after_branch_label'), SiIconType::ICON_ANGLE_DOWN)
								->setTooltip($dtc->t('ei_impl_add_after_branch_tooltip'))
								->setImportant(true),
						['after', $eiuEntry->getPid()]));
		$groupControl->putGuiControl(self::CONTROL_INSERT_CHILD_KEY,
				$eiuControlFactory->newCmdRef(
						SiButton::success($dtc->translate('ei_impl_add_child_branch_label'), SiIconType::ICON_ANGLE_RIGHT)
								->setTooltip($dtc->translate('ei_impl_add_child_branch_tooltip'))
								->setImportant(true),
						['child', $eiuEntry->getPid()]));
		
		return [self::CONTROL_INSERT_BRANCH_KEY => $groupControl];
	}

	function buildAddNavPoint(Eiu $eiu): ?SiNavPoint {
		return SiNavPoint::siref();
	}
}
