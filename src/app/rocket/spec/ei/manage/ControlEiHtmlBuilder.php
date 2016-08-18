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
namespace rocket\spec\ei\manage;

use n2n\web\ui\view\impl\html\HtmlView;
use n2n\web\ui\view\impl\html\HtmlElement;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\model\EntryGuiModel;
use n2n\web\ui\UiComponent;

class ControlEiHtmlBuilder {
	private $view;
	private $eiState;

	public function __construct(HtmlView $view, EiState $eiState) {
		$this->view = $view;
		$this->eiState = $eiState;
	}
	
	public function overallControlList() {
		$this->view->out($this->getOverallControlList());
	}
	
	public function getOverallControlList(): UiComponent {
		$ul = new HtmlElement('ul'/*, array('class' => 'rocket-main-controls')*/);
		foreach ($this->eiState->getContextEiMask()->createOverallHrefControls($this->eiState, $this->view) as $control) {
			$ul->appendContent(new HtmlElement('li', null, $control->createUiComponent(false)));
		}
	
		return $ul;
	}
	
	public function entryControlList(EntryGuiModel $entryGuiModel, $useIcons = false) {
		$this->view->getHtmlBuilder()->out($this->getEntryControlList($entryGuiModel, $useIcons));
	}
	
	public function getEntryControlList(EntryGuiModel $entryGuiModel, $useIcons = false) {
		$entryControls = $this->eiState->getContextEiMask()->createEntryHrefControls($entryGuiModel, $this->eiState, $this->view);
	
		$ulHtmlElement = new HtmlElement('ul', array('class' => ($useIcons ? 'rocket-simple-controls' : null /* 'rocket-main-controls' */)));
	
		foreach ($entryControls as $control) {
			$liHtmlElement = new HtmlElement('li', null, $control->createUiComponent($useIcons));
			$ulHtmlElement->appendContent($liHtmlElement);
		}
	
		return $ulHtmlElement;
	}
}
