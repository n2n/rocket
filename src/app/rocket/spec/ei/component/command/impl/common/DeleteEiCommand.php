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

use n2n\core\N2N;
use n2n\l10n\DynamicTextCollection;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\EiState;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\component\command\control\PartialControlComponent;
use rocket\spec\ei\manage\control\EntryControlComponent;
use rocket\spec\ei\component\command\impl\common\controller\DeleteController;
use rocket\spec\ei\manage\control\ControlButton;
use rocket\spec\ei\manage\control\IconType;
use rocket\spec\ei\component\command\impl\IndependentEiCommandAdapter;
use rocket\spec\ei\component\command\PrivilegedEiCommand;
use rocket\spec\ei\manage\model\EntryGuiModel;
use rocket\spec\security\impl\CommonEiCommandPrivilege;
use n2n\core\container\N2nContext;
use rocket\core\model\Rocket;
use rocket\spec\security\EiCommandPrivilege;
use n2n\l10n\Lstr;
use rocket\spec\ei\manage\control\HrefControl;
use rocket\spec\ei\manage\util\model\EiStateUtils;
use n2n\util\uri\Path;
use rocket\spec\ei\manage\util\model\EntryGuiUtils;

class DeleteEiCommand extends IndependentEiCommandAdapter implements PartialControlComponent, 
		EntryControlComponent, PrivilegedEiCommand {
	const ID_BASE = 'delete';
	const CONTROL_BUTTON_KEY = 'delete'; 
	const PRIVILEGE_LIVE_ENTRY_KEY = 'liveEntry';
	const PRIVILEGE_DRAFT_KEY = 'draft';
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName(): string {
		return 'Delete';
	}
		
	public function lookupController(EiState $eiState) {
		return $eiState->getN2nContext()->lookup(DeleteController::class);
	}
	
	public function createEntryHrefControls(EntryGuiUtils $entryGuiUtils, HtmlView $view): array {
		$entryGuiUtils = new EntryGuiUtils($entryGuiModel, $eiState);
		
		$pathExt = null;
		$name = null;
		$tooltip = null;
		$confirmMessage = null;
		if ($entryGuiUtils->isDraft()) {
			$draft = $entryGuiUtils->getDraft();
			$pathExt = new Path(array('draft', $draft->getId()));
			$name = $view->getL10nText('ei_impl_delete_draft_label');
			$tooltip = $view->getL10nText('ei_impl_delete_draft_tooltip', 
					array('last_mod' => $view->getL10nDateTime($draft->getLastMod())));
			$confirmMessage = $view->getL10nText('ei_impl_delete_draft_confirm_message', 
					array('last_mod' => $view->getL10nDateTime($draft->getLastMod())));
		} else {
			$eiUtils = new EiStateUtils($eiState);
			$pathExt = new Path(array('live', $entryGuiUtils->getIdRep()));
			$identityString = $entryGuiUtils->createIdentityString();
			$name = $view->getL10nText('common_delete_label');
			$tooltip = $view->getL10nText('ei_impl_delete_entry_tooltip', array('entry' => $eiUtils->getGenericLabel()));
			$confirmMessage = $view->getL10nText('ei_impl_delete_entry_confirm', array('entry' => $identityString));
		}
		
		$controlButton = new ControlButton($name, $tooltip, false, ControlButton::TYPE_DANGER, IconType::ICON_TIMES);
		$controlButton->setConfirmMessage($confirmMessage);
		$controlButton->setConfirmOkButtonLabel($view->getL10nText('common_yes_label'));
		$controlButton->setConfirmCancelButtonLabel($view->getL10nText('common_no_label'));
		
		$query = array();
		if ($entryGuiUtils->isViewModeOverview()) {
			$query['refPath'] = (string) $eiState->getCurrentUrl($view->getHttpContext());
		}
		
		$hrefControl = HrefControl::create($eiState, $this, $pathExt->toUrl($query), $controlButton);
		
		return array(self::CONTROL_BUTTON_KEY => $hrefControl);
	}
	
	public function getEntryControlOptions(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket');
		
		return array(self::CONTROL_BUTTON_KEY => $dtc->translate('ei_impl_delete_draft_label'));
	}
	
	public function createPartialControlButtons(EiState $eiState, HtmlView $htmlView) {
		$dtc = new DynamicTextCollection('rocket', $htmlView->getN2nContext()->getN2nLocale());
		$eiCommandButton = new ControlButton(null, $dtc->translate('ei_impl_partial_delete_label'), 
				$dtc->translate('ei_impl_partial_delete_tooltip'), false, ControlButton::TYPE_DEFAULT,
				IconType::ICON_TIMES_SIGN);
		$eiCommandButton->setConfirmMessage($dtc->translate('ei_impl_partial_delete_confirm_message'));
		$eiCommandButton->setConfirmOkButtonLabel($dtc->translate('common_yes_label'));
		$eiCommandButton->setConfirmCancelButtonLabel($dtc->translate('common_no_label'));
		
		return array(self::CONTROL_BUTTON_KEY => $eiCommandButton);
	}
	
	public function getPartialControlOptions(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket');
		
		return array(self::CONTROL_BUTTON_KEY => $dtc->translate('ei_impl_partial_delete_label'));
	}
	
	public function processEntries(EiState $eiState, array $entries) {
		$scriptManager = N2N::getModelContext()->lookup('rocket\spec\config\SpecManager');
		$eiSpec = $this->getEiSpec();
		$em = $eiState->getEntityManager();
		
		foreach ($entries as $entry) {
// 			$scriptManager->notifyOnDelete($entry);
			$em->remove($entry);
// 			$scriptManager->notifyDelete($entry);
		}
	}


	public function createEiCommandPrivilege(N2nContext $n2nContext): EiCommandPrivilege {
		$pi = new CommonEiCommandPrivilege(new Lstr('common_delete_label', Rocket::NS));
		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_LIVE_ENTRY_KEY,
				new CommonEiCommandPrivilege(new Lstr('ei_impl_delete_live_entry_label', Rocket::NS)));
		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_DRAFT_KEY,
				new CommonEiCommandPrivilege(new Lstr('ei_impl_delete_draft_label', Rocket::NS)));
		return $pi;
	}
	
// 	public static function createPathExt($entityId, $draftId = null) {
// 		if (isset($draftId)) {
// 			return self::createHistoryPathExt($draftId);
// 		}
	
// 		return new Path(array($this->getId(), $entityId));
// 	}
	
// 	public static function createHistoryPathExt($draftId) {
// 		return new Path(array($this->getId(), 'draft', $draftId));
// 	}
}
