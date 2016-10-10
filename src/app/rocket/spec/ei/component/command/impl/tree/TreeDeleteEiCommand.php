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
namespace rocket\spec\ei\component\command\impl\tree;

// use rocket\spec\ei\component\command\impl\tree\controller\TreeDeleteController;
// use n2n\l10n\DynamicTextCollection;
// use n2n\impl\web\ui\view\html\HtmlView;
// use rocket\spec\ei\manage\EiState;
// use n2n\l10n\N2nLocale;
// use rocket\spec\ei\manage\control\EntryControlComponent;
// use rocket\spec\ei\manage\control\ControlButton;
// use rocket\spec\ei\manage\control\IconType;
// use rocket\spec\ei\component\command\impl\IndependentEiCommandAdapter;
// use rocket\spec\ei\component\command\impl\common\controller\PathUtils;
// use rocket\spec\ei\manage\model\EntryModel;
// use rocket\spec\ei\manage\model\EntryGuiModel;
// use rocket\spec\ei\component\command\PrivilegedEiCommand;
// use n2n\core\container\N2nContext;
// use rocket\spec\security\impl\CommonEiCommandPrivilege;
// use rocket\core\model\Rocket;
// use rocket\spec\security\EiCommandPrivilege;
// use n2n\l10n\Lstr;

// class TreeDeleteEiCommand extends IndependentEiCommandAdapter implements EntryControlComponent, PrivilegedEiCommand {
// 	const ID_BASE = 'tree-delete';
// 	const CONTROL_BUTTON_KEY = 'delete'; 
// 	const PRIVILEGE_LIVE_ENTRY_KEY = 'liveEntry';
// 	const PRIVILEGE_DRAFT_KEY = 'draft';
	
// 	public function getIdBase() {
// 		return self::ID_BASE;
// 	}
	
// 	public function getTypeName(): string {
// 		return 'Tree Delete';
// 	}
		
// 	public function lookupController(EiState $eiState) {
// 		$treeDeleteController = new TreeDeleteController();
// 		TreeUtils::initializeController($this, $treeDeleteController);
// 		return $treeDeleteController;
// 	}
	
// 	public function getEntryControlOptions(N2nLocale $n2nLocale) {
// 		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
// 		return array(self::ID_BASE => $dtc->translate('ei_impl_delete_branch_label'));
// 	}
	
// 	public function createEntryHrefControls(EiuGui $entryGuiUtils, HtmlView $view): array {
		
// 		$eiMapping = $entryGuiModel->getEiMapping();
				
// 		$eiSelection = $eiMapping->getEiSelection();
// 		$request = $view->getRequest();
// 		$dtc = new DynamicTextCollection('rocket', $request->getN2nLocale());
	
		
// 		$pathExt = null;
// 		$name = null;
// 		$tooltip = null;
// 		$confirmMessage = null;
// 		if ($eiSelection->isDraft()) {
// 			$draft = $eiSelection->getDraft();
// 			$pathExt = PathUtils::createDraftPathExt($this->getId(), $eiSelection->getId(), $draft->getId());
// 			$name = $dtc->translate('ei_impl_tree_delete_draft_label');
// 			$tooltip = $dtc->translate('ei_impl_tree_delete_draft_tooltip',
// 					array('last_mod' => $view->getL10nDateTime($draft->getLastMod())));
// 			$confirmMessage = $dtc->translate('ei_impl_tree_delete_draft_confirm_message',
// 					array('last_mod' => $view->getL10nDateTime($draft->getLastMod())));
// 		}  else {
// 			$pathExt = PathUtils::createPathExt($this->getId(), $eiSelection->getLiveEntry()->getId());
// 			$identityString = $entryGuiModel->getEiMask()->createIdentityString($eiSelection, $request->getN2nLocale());
// 			$name = $dtc->translate('ei_impl_delete_branch_label');
// 			$tooltip = $dtc->translate('ei_impl_delete_branch_tooltip', array('entry' => $identityString));
// 			$confirmMessage = $dtc->translate('ei_impl_tree_delete_confirm_message', array('entry' => $identityString));
// 		}
		
		
// 		$eiCommandButton = new ControlButton(
// 				$request->getControllerContextPath($eiState->getControllerContext())->ext($pathExt)
// 						->toUrl(/* array('previewtype' => $eiState->getPreviewType()) */),
// 				$name, $tooltip, false, ControlButton::TYPE_DANGER,
// 				IconType::ICON_TIMES);
// 		$eiCommandButton->setConfirmMessage($confirmMessage);
// 		$eiCommandButton->setConfirmOkButtonLabel($dtc->translate('common_yes_label'));
// 		$eiCommandButton->setConfirmCancelButtonLabel($dtc->translate('common_no_label'));
		
// 		return array(self::CONTROL_BUTTON_KEY => $eiCommandButton);
// 	}
	
// 	public function createEiCommandPrivilege(N2nContext $n2nContext): EiCommandPrivilege {
// 		$pi = new CommonEiCommandPrivilege(new Lstr('ei_impl_tree_delete_label', Rocket::NS));
// 		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_LIVE_ENTRY_KEY,
// 				new CommonEiCommandPrivilege(new Lstr('ei_impl_tree_delete_live_entry_label', Rocket::NS)));
// 		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_DRAFT_KEY,
// 				new CommonEiCommandPrivilege(new Lstr('ei_impl_tree_delete_draft_label', Rocket::NS)));
// 		return $pi;
// 	}
// }
