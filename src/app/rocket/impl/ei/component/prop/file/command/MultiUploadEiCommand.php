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
namespace rocket\impl\ei\component\prop\file\command;

use rocket\impl\ei\component\command\EiCommandAdapter;
use n2n\l10n\N2nLocale;
use n2n\l10n\DynamicTextCollection;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\control\ControlButton;
use rocket\ei\manage\control\IconType;
use rocket\impl\ei\component\prop\file\command\controller\MultiUploadEiController;
use rocket\ei\component\command\control\OverallControlComponent;
use rocket\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use rocket\ei\manage\control\HrefControl;
use rocket\impl\ei\component\prop\file\FileEiProp;
use rocket\ei\EiPropPath;

class MultiUploadEiCommand extends EiCommandAdapter implements OverallControlComponent {
	const MULTI_UPLOAD_KEY = 'multi-upload';
	/**
	 * @var \rocket\impl\ei\component\prop\file\FileEiProp
	 */
	private $fileEiProp;
	private $namingEiPropPath;
	
	public function __construct(FileEiProp $fileEiProp, EiPropPath $namingEiPropPath = null, string $order = null) {
		$this->fileEiProp = $fileEiProp;
		$this->namingEiPropPath = $namingEiPropPath;
		$this->order = $order;
	}

	public function lookupController(Eiu $eiu): Controller {
		$controller = new MultiUploadEiController();
		$controller->setFileEiProp($this->fileEiProp);
		$controller->setOrder($this->order);
		return $controller;
	}
	
	public function getOverallControlOptions(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket');
		return array(self::MULTI_UPLOAD_KEY => $dtc->translate('ei_impl_multi_upload_label'));
	}

	public function createOverallControls(Eiu $eiu, HtmlView $view): array {
		$request = $view->getRequest();
		$dtc = new DynamicTextCollection('rocket', $eiu->frame()->getN2nLocale());
		
		$name = $dtc->translate('ei_impl_multi_upload_label');
		$tooltip = $dtc->translate('ei_impl_multi_upload_tooltip');
		
		return array(self::MULTI_UPLOAD_KEY => HrefControl::create($eiu->frame()->getEiFrame(), $this, null,
				new ControlButton($name, $tooltip, true, ControlButton::TYPE_SECONDARY, IconType::ICON_UPLOAD)));
	}
}
