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

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\impl\web\ui\view\html\HtmlElement;
use rocket\spec\ei\manage\model\EntryGuiModel;
use n2n\web\ui\UiComponent;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\config\mask\model\CommonEntryGuiModel;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\manage\util\model\EiuGui;
use rocket\spec\ei\manage\util\model\EiuEntry;

class ControlEiHtmlBuilder {
	private $view;
	private $eiFrame;

	public function __construct(HtmlView $view, EiFrame $eiFrame) {
		$this->view = $view;
		$this->eiFrame = $eiFrame;
	}
	
	public function overallControlList() {
		$this->view->out($this->getOverallControlList());
	}
	
	public function getOverallControlList(): UiComponent {
		$ul = new HtmlElement('ul'/*, array('class' => 'rocket-main-controls')*/);
		foreach ($this->eiFrame->getContextEiMask()->createOverallHrefControls($this->eiFrame, $this->view) as $control) {
			$ul->appendContent(new HtmlElement('li', null, $control->createUiComponent(false)));
		}
	
		return $ul;
	}
	
	public function entryGuiControlList(EntryGuiModel $entryGuiModel, bool $useIcons = false) {
		$this->view->out($this->getEntryGuiControlList($entryGuiModel, $useIcons));
	}
	
	public function getEntryGuiControlList(EntryGuiModel $entryGuiModel, bool $useIcons = false) {
		$entryControls = $this->eiFrame->getContextEiMask()->createEntryHrefControls(
				new EiuGui($entryGuiModel, new EiuEntry($entryGuiModel, $this->eiFrame)), $this->view);
	
		return $this->createControlList($entryControls, $useIcons);
	}
	
	public function entryControlList($eiEntryObj, int $viewMode, bool $useIcons = false) {
		$this->view->out($this->getEntryControlList($eiEntryObj, $viewMode, $useIcons));
	}
	
	public function getEntryControlList($eiEntryObj, int $viewMode, $useIcons = false) {
		$entryGuiUtils = new EiuGui($eiEntryObj, $viewMode, $this->eiFrame);
		return $this->createControlList($this->eiFrame->getContextEiMask()
				->createEntryHrefControls($entryGuiUtils, $this->view), $useIcons);
	}
	
	private function createControlList(array $entryControls, bool $useIcons) {
		$ulHtmlElement = new HtmlElement('ul', array('class' => ($useIcons ? 'rocket-simple-controls' : null /* 'rocket-main-controls' */)));
		
		foreach ($entryControls as $control) {
			$liHtmlElement = new HtmlElement('li', null, $control->createUiComponent($useIcons));
			$ulHtmlElement->appendContent($liHtmlElement);
		}
		
		return $ulHtmlElement;
	}
}