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
namespace rocket\spec\ei\component\field\impl\bool\command;

use rocket\spec\ei\component\field\impl\bool\OnlineEiField;
use n2n\l10n\DynamicTextCollection;
use rocket\spec\ei\manage\EiState;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\control\EntryControlComponent;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\control\IconType;
use rocket\spec\ei\manage\control\ControlButton;
use rocket\spec\ei\component\command\impl\EiCommandAdapter;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\model\EntryGuiModel;
use rocket\spec\ei\manage\control\HrefControl;
use rocket\core\model\Rocket;

class OnlineEiCommand extends EiCommandAdapter implements EntryControlComponent {
	const CONTROL_KEY = 'online_status';
	const ID_BASE = 'online-status';
	
	private $onlineEiField;
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName(): string {
		return 'Online Status';
	}
	
	public function setOnlineEiField(OnlineEiField $onlineEiField) {
		$this->onlineEiField = $onlineEiField;
	}
		
	public function lookupController(EiState $eiState) {
		$controller = $eiState->getN2nContext()->lookup(OnlineController::class);
		$controller->setOnlineEiField($this->onlineEiField);
		return $controller;
	}
	
	public function createEntryHrefControls(EntryGuiModel $entryGuiModel, EiState $eiState, 
			HtmlView $view): array {
		$eiMapping = $entryGuiModel->getEiMapping();
		$request = $view->getRequest();
		$dtc = new DynamicTextCollection(Rocket::NS, $request->getN2nLocale());
		$eiSelection = $eiMapping->getEiSelection();

		$controlButton = new ControlButton($dtc->translate('ei_impl_online_offline_label'), 
					$dtc->translate('ei_impl_online_offline_tooltip'));
		
		if ($eiMapping->getValue($this->onlineEiField)) {
			$controlButton->setType(ControlButton::TYPE_SUCCESS);
			$controlButton->setIconType(IconType::ICON_CHECK_CIRCLE);
		} else {
			$controlButton->setType(ControlButton::TYPE_DANGER);
			$controlButton->setIconType(IconType::ICON_MINUS_CIRCLE);
		}
		
		$contextPath = $view->getHttpContext()->getControllerContextPath($eiState->getControllerContext());
		$controlButton->setAttrs(array('class' => 'rocket-online-cmd',
				'data-online-url' => (string) $contextPath->ext($this->getId(), 'online', $eiMapping->getIdRep()),
				'data-offline-url' => (string) $contextPath->ext($this->getId(), 'offline', $eiMapping->getIdRep())));
		
		$view->getHtmlBuilder()->meta()->addJs('js/script/impl/online.js', Rocket::NS);

		return array(self::CONTROL_KEY => new HrefControl(null, $controlButton));
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\control\EntryControlComponent::getEntryControlOptions()
	 */
	public function getEntryControlOptions(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_KEY => $dtc->translate('ei_impl_online_set_label'));
	}
}
