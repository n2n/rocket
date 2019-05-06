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
use n2n\l10n\N2nLocale;
use rocket\si\control\SiButton;
use rocket\si\control\SiIconType;
use rocket\impl\ei\component\command\IndependentEiCommandAdapter;
use rocket\ei\component\command\PrivilegedEiCommand;
use n2n\core\container\N2nContext;
use rocket\core\model\Rocket;
use rocket\ei\util\Eiu;
use rocket\ei\manage\security\privilege\EiCommandPrivilege;
use n2n\web\http\controller\Controller;
use rocket\impl\ei\component\command\common\controller\AddController;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\ex\IllegalStateException;
use n2n\impl\web\dispatch\mag\model\MagForm;
use rocket\ei\component\EiSetup;
use n2n\util\type\CastUtils;
use rocket\impl\ei\component\EiConfiguratorAdapter;
use rocket\ei\component\EiConfigurator;

class AddEiCommand extends IndependentEiCommandAdapter implements PrivilegedEiCommand {
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

	public function getIdBase(): ?string {
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

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\PrivilegedEiCommand::createEiCommandPrivilege()
	 */
	public function createEiCommandPrivilege(Eiu $eiu): EiCommandPrivilege {
		$dtc = $eiu->dtc(Rocket::NS);
		$eiuCommandPrivilege = $eiu->factory()->newCommandPrivilege($dtc->t('common_new_entry_label'));
		
		$eiuCommandPrivilege->newSub(self::PRIVILEGE_LIVE_ENTRY_KEY, $dtc->t('ei_impl_add_live_entry_label'));
		$eiuCommandPrivilege->newSub(self::PRIVILEGE_DRAFT_KEY, $dtc->t('ei_impl_add_draft_label'));
		
		return $eiuCommandPrivilege;
		
// 		$pi = new CommonEiCommandPrivilege(Rocket::createLstr('common_new_entry_label', Rocket::NS));
// 		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_LIVE_ENTRY_KEY,
// 				new CommonEiCommandPrivilege(Rocket::createLstr('ei_impl_add_live_entry_label', Rocket::NS)));
// 		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_DRAFT_KEY,
// 				new CommonEiCommandPrivilege(Rocket::createLstr('ei_impl_add_draft_label', Rocket::NS)));
// 		return $pi;
	}

	public function lookupController(Eiu $eiu): Controller {
		return $eiu->lookup(AddController::class);
	}

	public function getOverallControlOptions(N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);

		$options = array();
		
		if (null === $this->eiMask->getEiType()->getNestedSetStrategy()) {
			$options[self::CONTROL_ADD_KEY] = $dtc->t('common_new_entry_label');
		} else {
			$options[self::CONTROL_ADD_ROOT_BRANCH_KEY] = $dtc->t('ei_impl_add_root_branch_label');
		}

		$options[self::CONTROL_ADD_DRAFT_KEY] = $dtc->translate('common_add_draft_label');
		
		return $options;
	}

	public function createOverallControls(Eiu $eiu): array {
		$eiuControlFactory = $eiu->gui()->controlFactory($this);
		$dtc = $eiu->dtc(Rocket::NS);
		
		$nestedSet = null !== $this->getWrapper()->getEiCommandCollection()->getEiMask()->getEiType()->getNestedSetStrategy();
		
		$controls = array();
		
		$key = $nestedSet ? self::CONTROL_ADD_ROOT_BRANCH_KEY : self::CONTROL_ADD_KEY;
		$controls[$key] = $eiuControlFactory->createJhtml(new SiButton(
				$dtc->t($nestedSet ? 'ei_impl_add_root_branch_label' : 'common_new_entry_label'),
				null, true, SiButton::TYPE_SUCCESS, SiIconType::ICON_PLUS_CIRCLE));
		
		if ($eiu->frame()->isDraftingEnabled()) {
			$controls[self::CONTROL_ADD_DRAFT_KEY] = $eiuControlFactory->createJhtml(new SiButton(
					$dtc->translate('ei_impl_add_draft_label'),
					null, true, SiButton::TYPE_SUCCESS, SiIconType::ICON_PLUS_CIRCLE));
		}
		
		return $controls;
	}

	public function getEntryGuiControlOptions(N2nContext $n2nContext, N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		
		return array(self::CONTROL_INSERT_BRANCH_KEY => $dtc->t('ei_impl_insert_branch_label'));
	}

	public function createEntryGuiControls(Eiu $eiu): array {
		if ($eiu->frame()->isExecutedBy($this)) {
			return array();
		}
		
		$eiuControlFactory = $eiu->gui()->controlFactory($this);
		$eiuEntry = $eiu->entry();
		$dtc = $eiu->dtc(Rocket::NS);
		
		if ($eiu->frame()->getNestedSetStrategy() === null) {
			if (!$this->dublicatingAllowed) return array();
			
			$name = $dtc->t('ei_impl_duplicate_label');
			$tooltip = $dtc->t('ei_impl_duplicate_tooltip', array('entry' => $eiuEntry->createIdentityString()));
			$siButton = new SiButton($name, $tooltip, false, SiButton::TYPE_SUCCESS, SiIconType::ICON_COPY);
			
			return array($eiuControlFactory
					->createCmdRef(self::CONTROL_DUPLICATE_KEY , $siButton, [$eiuEntry->getPid()]));
		}


		$groupControl = $eiuControlFactory->createGroup(new SiButton($dtc->t('ei_impl_insert_branch_label'),
						$dtc->t('ei_impl_add_branch_tooltip'), false, SiButton::TYPE_SECONDARY, 
						SiIconType::ICON_PLUS));
		
		$groupControl->add(
				 $eiuControlFactory->createJhtml(
						new SiButton($dtc->t('ei_impl_add_before_branch_label'),
								$dtc->t('ei_impl_add_before_branch_tooltip'),
								true, SiButton::TYPE_SUCCESS, SiIconType::ICON_ANGLE_UP),
						['before', $eiuEntry->getPid()]),
				$eiuControlFactory->createJhtml(
						new SiButton($dtc->t('ei_impl_add_after_branch_label'),
								$dtc->t('ei_impl_add_after_branch_tooltip'),
								true, SiButton::TYPE_SUCCESS, SiIconType::ICON_ANGLE_DOWN),
						['after', $eiuEntry->getPid()]),
				$eiuControlFactory->createJhtml(
						new SiButton($dtc->translate('ei_impl_add_child_branch_label'),
								$dtc->translate('ei_impl_add_child_branch_tooltip'),
								true, SiButton::TYPE_SUCCESS, SiIconType::ICON_ANGLE_RIGHT),
						['child', $eiuEntry->getPid()]));
		
		return array(self::CONTROL_INSERT_BRANCH_KEY => $groupControl);
	}
	
	public function createEiConfigurator(): EiConfigurator {
		return new AddEiConfigurator($this);
	}
}
	

class AddEiConfigurator extends EiConfiguratorAdapter {
	const OPTION_DUPLICATE_ALLOWED_KEY = 'duplicateAllowed';
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$eiComponent = $this->eiComponent;
		IllegalStateException::assertTrue($eiComponent instanceof AddEiCommand);
		
		$magCollection = new MagCollection();
		$magCollection->addMag(self::OPTION_DUPLICATE_ALLOWED_KEY, new BoolMag('Duplicating Allowed', 
				$this->getAttributes()->get(
						self::OPTION_DUPLICATE_ALLOWED_KEY, false, $eiComponent->isDublicatingAllowed())));
		return new MagForm($magCollection);
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		$this->attributes->set(self::OPTION_DUPLICATE_ALLOWED_KEY, $magDispatchable->getPropertyValue(self::OPTION_DUPLICATE_ALLOWED_KEY));
	}
	
	public function setup(EiSetup $eiSetupProcess) {
		$eiComponent = $this->eiComponent;
		CastUtils::assertTrue($eiComponent instanceof AddEiCommand);
		
		$eiComponent->setDublicatingAllowed($this->attributes->optBool(self::OPTION_DUPLICATE_ALLOWED_KEY,
				$eiComponent->isDublicatingAllowed()));
	}
}

