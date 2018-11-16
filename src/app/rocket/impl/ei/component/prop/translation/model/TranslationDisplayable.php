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
namespace rocket\impl\ei\component\prop\translation\model;

use rocket\ei\manage\gui\GuiField;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\gui\GuiField;
use rocket\ei\manage\gui\GuiFieldEditable;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\GuiProp;

class TranslationDisplayable implements GuiField {
	private $guiProp;
	private $localeDefs;
	private $translatedDisplayables = array();
	
	public function __construct(GuiProp $guiProp, array $localeDefs) {
		$this->guiProp = $guiProp;
		$this->localeDefs = $localeDefs;
	}
	
	
	public function isEmpty() {
		return empty($this->translatedDisplayables);
	}
	
	public function putDisplayable($n2nLocaleId, Displayable $translatedDisplayable) {
		$this->translatedDisplayables[$n2nLocaleId] = $translatedDisplayable;
	}
	
	public function isMandatory(): bool {
		foreach ($this->translatedDisplayables as $translatedDisplayable) {
			if ($translatedDisplayable->isMandatory()) return true;
		}
		
		return false;
	}
	
	public function isReadOnly(): bool {
		foreach ($this->translatedDisplayables as $translatedDisplayable) {
			if (!$translatedDisplayable->isReadOnly()) return false;
		}		
		
		return true;
	}
	
	public function getOutputHtmlContainerAttrs(): array {
		return array();
	}
	
	public function createOutputUiComponent(HtmlView $view) {
// 		$outputUiComponents = array();
// 		foreach ($this->translatedDisplayables as $n2nLocaleId => $translatedDisplayable) {
// 			$outputUiComponents[$n2nLocaleId] = $translatedDisplayable->createOutputUiComponent($view);
// 		}
		
		return $view->getImport('\rocket\impl\ei\component\prop\translation\view\displayable.html',
				array('displayables' => $this->translatedDisplayables, 'label' => $this->guiProp->getDisplayLabelLstr()->t($view->getN2nLocale()),
						'localeDefs' => $this->localeDefs));
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::getEditable()
	 */
	public function getEditable(): GuiFieldEditable {
		throw new IllegalStateException();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::getDisplayItemType()
	 */
	public function getDisplayItemType(): ?string {
		return null;
	}
}