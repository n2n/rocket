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

use n2n\ui\view\impl\html\HtmlView;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\GuiDefinition;
use n2n\ui\UiComponent;
use n2n\ui\view\impl\html\HtmlElement;
use n2n\ui\view\impl\html\HtmlUtils;

class EiHtmlBuilder {
	private $view;
	private $guiDefinition;
	
	public function __construct(HtmlView $view, GuiDefinition $guiDefinition) {
		$this->view = $view;
		$this->guiDefinition = $guiDefinition;
	}
	
	public function simpleLabel($guiIdPath) {
		$this->view->getHtmlBuilder()->out($this->getSimpleLabel($guiIdPath));
	}
	
	public function getSimpleLabel($guiIdPath): UiComponent {
		return $this->view->getHtmlBuilder()->getEsc($this->guiDefinition->getGuiFieldByGuiIdPath(
				GuiIdPath::createFromExpression($guiIdPath))->getDisplayLabel());
	}
	
	public function generalEntrySelector(string $containerTagName, array $containerAttrs = null, $content = '') {
		$this->view->out($this->getGeneralEntrySelector($containerTagName, $containerAttrs, $content));
	}
	
	public function getGeneralEntrySelector(string $containerTagName, array $containerAttrs = null, $content = ''): UiComponent {
		return new HtmlElement($containerTagName, 
				HtmlUtils::mergeAttrs(array('class' => 'rocket-general-entry-selector'), (array) $containerAttrs),
				$content);
	}
}
