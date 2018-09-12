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
namespace rocket\ei\manage;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\web\ui\UiComponent;
use rocket\ei\util\gui\EiuEntryGui;
use rocket\ei\util\EiuAnalyst;
use n2n\impl\web\ui\view\html\HtmlSnippet;

class ControlEiuHtmlBuilder {
	private $view;
	private $eiuFrame;

	public function __construct(HtmlView $view, $eiuFrame) {
		$this->view = $view;
		$this->eiuFrame = EiuAnalyst::buildEiuFrameFormEiArg($eiuFrame);
	}
	
	public function overallControlList() {
		$this->view->out($this->getOverallControlList());
	}
	
	public function getOverallControlList(): UiComponent {
		$snippet = new HtmlSnippet();
		foreach ($this->eiuFrame->getEiFrame()->getContextEiEngine()->createOverallControls($this->eiuFrame, $this->view) as $control) {
			$snippet->appendLn($control->createUiComponent(false));
		}
	
		return $snippet;
	}
	
	public function entryGuiControlList(EiuEntryGui $eiuEntryGui, bool $useIcons = false) {
		$this->view->out($this->getEntryGuiControlList($eiuEntryGui, $useIcons));
	}
	
	public function getEntryGuiControlList(EiuEntryGui $eiuEntryGui, bool $useIcons = false) {
		$entryControls = $this->eiuFrame->getEiFrame()->getContextEiEngine()
				->createEntryControls($eiuEntryGui, $this->view);
	
		return $this->createControlList($entryControls, $useIcons);
	}
	
	public function entryControlList(EiuEntryGui $eiuEntryGui, int $viewMode, bool $useIcons = false) {
		$this->view->out($this->getEntryControlList($eiuEntryGui, $viewMode, $useIcons));
	}
	
	public function getEntryControlList(EiuEntryGui $eiuEntryGui, $useIcons = false) {
		return $this->createControlList($this->eiuFrame->getContextEiMask()
				->createEntryControls($eiuEntryGui, $this->view), $useIcons);
	}
	
	private function createControlList(array $entryControls, bool $useIcons) {
		$divHtmlElement = new HtmlElement('div', array('class' => ($useIcons ? 'rocket-simple-commands' : null /* 'rocket-main-controls' */)));
		
		foreach ($entryControls as $control) {
			$divHtmlElement->appendContent($control->createUiComponent($useIcons));
		}
		
		return $divHtmlElement;
	}
}
