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

use rocket\spec\ei\component\field\impl\bool\OnlineEiProp;
use n2n\l10n\DynamicTextCollection;
use rocket\spec\ei\manage\EiFrame;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\control\EntryControlComponent;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\control\IconType;
use rocket\spec\ei\manage\control\ControlButton;
use rocket\spec\ei\component\command\impl\EiCommandAdapter;
use rocket\spec\ei\manage\mapping\EiEntry;
use rocket\spec\ei\manage\control\HrefControl;
use rocket\core\model\Rocket;
use rocket\spec\ei\manage\util\model\Eiu;
use n2n\web\http\controller\Controller;

class OnlineEiCommand extends EiCommandAdapter implements EntryControlComponent {
	const CONTROL_KEY = 'online_status';
	const ID_BASE = 'online-status';
	
	private $onlineEiProp;
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName(): string {
		return 'Online Status';
	}
	
	public function setOnlineEiProp(OnlineEiProp $onlineEiProp) {
		$this->onlineEiProp = $onlineEiProp;
	}
		
	public function lookupController(Eiu $eiu): Controller {
		$controller = $eiu->lookup(OnlineController::class);
		$controller->setOnlineEiProp($this->onlineEiProp);
		return $controller;
	}
	
	public function createEntryControls(Eiu $eiu, HtmlView $view): array {
		$eiEntry = $eiu->entry()->getEiEntry();
		$eiFrame = $eiu->frame()->getEiFrame();
		$request = $view->getRequest();
		$dtc = new DynamicTextCollection(Rocket::NS, $request->getN2nLocale());
		$eiObject = $eiEntry->getEiObject();

		$controlButton = new ControlButton($dtc->translate('ei_impl_online_offline_label'), 
					$dtc->translate('ei_impl_online_offline_tooltip'));
		
		if ($eiEntry->getValue($this->onlineEiProp)) {
			$controlButton->setType(ControlButton::TYPE_SUCCESS);
			$controlButton->setIconType(IconType::ICON_CHECK_CIRCLE);
		} else {
			$controlButton->setType(ControlButton::TYPE_DANGER);
			$controlButton->setIconType(IconType::ICON_MINUS_CIRCLE);
		}
		
		$contextPath = $view->getHttpContext()->getControllerContextPath($eiFrame->getControllerContext());
		$controlButton->setAttrs(array('class' => 'rocket-online-cmd',
				'data-online-url' => (string) $contextPath->ext($this->getId(), 'online', $eiEntry->getIdRep()),
				'data-offline-url' => (string) $contextPath->ext($this->getId(), 'offline', $eiEntry->getIdRep())));
		
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
