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
namespace rocket\spec\ei\component\field\impl\relation\model;

use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\gui\Editable;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\field\impl\relation\model\mag\ToOneMag;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\gui\GuiElement;
use rocket\spec\ei\manage\util\model\EiStateUtils;

class ManyToOneGuiElement implements GuiElement {
	private $label;
	private $toOneMappable;
	private $targetEiState;
	private $editable;
	private $toOneMag;
	
	public function __construct(string $label, ToOneMappable $toOneMappable, EiState $targetEiState, 
			Editable $editable = null) {
		$this->label = $label;
		$this->toOneMappable = $toOneMappable;
		$this->targetEiState = $targetEiState;
		$this->editable = $editable;
	}
	
	public function isReadOnly(): bool {
		return $this->editable === null;
	}
	
	/**
	 * @return string
	 */
	public function getUiOutputLabel(): string {
		return $this->label;
	}
	
	/**
	 * @return array
	 */
	public function getOutputHtmlContainerAttrs(): array {
		return array();
	}
	
	public function createOutputUiComponent(HtmlView $view) {
		$html = $view->getHtmlBuilder();
		$targetRelationEntry = $this->toOneMappable->getValue();
		
		if ($targetRelationEntry === null || $targetRelationEntry->isNew()) return null;
		
		$targetEiStateUtils = new EiStateUtils($this->targetEiState);
		$identityString = $targetEiStateUtils->createIdentityString($targetRelationEntry->getEiSelection());
		if (!$this->targetEiState->isDetailUrlAvailable($targetRelationEntry->getEiSelection()->toEntryNavPoint())) {
			return $html->getEsc($identityString);
		}
		
		return $html->getLink($this->targetEiState->getDetailUrl($view->getHttpContext(), $targetRelationEntry
						->getEiSelection()->toEntryNavPoint($this->targetEiState->getContextEiMask()->getEiEngine()->getEiSpec())), 
				$identityString);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiElement::createEditable()
	 */
	public function getEditable(): Editable {
		if ($this->editable !== null) {
			return $this->editable;
		}
		
		throw new IllegalStateException('GuiElement read only.');
	}
}
