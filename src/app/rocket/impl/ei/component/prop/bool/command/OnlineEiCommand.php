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
namespace rocket\impl\ei\component\prop\bool\command;

use rocket\impl\ei\component\prop\bool\OnlineEiProp;
use n2n\l10n\DynamicTextCollection;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\component\command\control\EntryControlComponent;
use n2n\l10n\N2nLocale;
use rocket\ei\manage\control\IconType;
use rocket\ei\manage\control\ControlButton;
use rocket\impl\ei\component\command\EiCommandAdapter;
use rocket\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use n2n\util\uri\Path;
use n2n\core\container\N2nContext;

class OnlineEiCommand extends EiCommandAdapter implements EntryControlComponent {
	const CONTROL_KEY = 'online_status';
	const ID_BASE = 'online-status';
	
	private $onlineEiProp;
	
	public function getIdBase(): ?string {
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
		$controller->setOnlineEiCommand($this);
		return $controller;
	}
	
	/**
	 * @param Eiu $eiu
	 * @return \rocket\ei\manage\control\JhtmlControl
	 */
	public function createEntryControl(Eiu $eiu) {
		$eiuControlFactory = $eiu->frame()->controlFactory($this);
		
		$eiuEntry = $eiu->entry();
		$eiuFrame = $eiu->frame();
		$dtc = new DynamicTextCollection('rocket', $eiuFrame->getN2nLocale());
		
		$controlButton = new ControlButton($dtc->t('ei_impl_online_offline_label'),
				$dtc->t('ei_impl_online_offline_tooltip', array('entry' => $eiuFrame->getGenericLabel())));
		$controlButton->setIconImportant(true);
		
		$urlExt = null;
		if ($eiuEntry->getValue($this->onlineEiProp)) {
			$controlButton->setType(ControlButton::TYPE_SUCCESS);
			$controlButton->setIconType(IconType::ICON_CHECK_CIRCLE);
			$urlExt = (new Path(array('offline', $eiuEntry->getPid())))->toUrl();
		} else {
			$controlButton->setType(ControlButton::TYPE_DANGER);
			$controlButton->setIconType(IconType::ICON_MINUS_CIRCLE);
			$urlExt = (new Path(array('online', $eiuEntry->getPid())))->toUrl();
		}
		
		return $eiuControlFactory->createJhtml($controlButton, $urlExt)
				->setForceReload(true)->setPushToHistory(false);
	}
	
	public function createEntryControls(Eiu $eiu, HtmlView $view): array {
		return array(self::CONTROL_KEY => $this->createEntryControl($eiu));
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\component\command\control\EntryControlComponent::getEntryControlOptions()
	 */
	public function getEntryControlOptions(N2nContext $n2nContext, N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_KEY => $dtc->translate('ei_impl_online_set_label'));
	}
}
