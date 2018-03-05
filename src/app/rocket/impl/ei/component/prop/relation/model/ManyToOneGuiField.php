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
namespace rocket\impl\ei\component\prop\relation\model;

use rocket\ei\manage\EiFrame;
use rocket\ei\manage\gui\GuiFieldEditable;
use n2n\util\ex\IllegalStateException;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\gui\GuiField;
use rocket\ei\manage\util\model\EiuFrame;

class ManyToOneGuiField implements GuiField {
	private $label;
	private $toOneEiField;
	private $targetEiFrame;
	private $editable;
	private $toOneMag;
	
	public function __construct(string $label, ToOneEiField $toOneEiField, EiFrame $targetEiFrame, 
			GuiFieldEditable $editable = null) {
		$this->label = $label;
		$this->toOneEiField = $toOneEiField;
		$this->targetEiFrame = $targetEiFrame;
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
	
	public function getDisplayItemType() {
		return null;
	}
	
	public function createOutputUiComponent(HtmlView $view) {
		$html = $view->getHtmlBuilder();
		$targetRelationEntry = $this->toOneEiField->getValue();
		
		if ($targetRelationEntry === null || $targetRelationEntry->isNew()) return null;
		
		$targetEiuFrame = new EiuFrame($this->targetEiFrame);
		$identityString = $targetEiuFrame->createIdentityString($targetRelationEntry->getEiObject());
		if (!$this->targetEiFrame->isDetailUrlAvailable($targetRelationEntry->getEiObject()->toEntryNavPoint())) {
			return $html->getEsc($identityString);
		}
		
		return $html->getLink($this->targetEiFrame->getDetailUrl($view->getHttpContext(), $targetRelationEntry
						->getEiObject()->toEntryNavPoint()), 
				$identityString, array('data-jhtml' => 'true'));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::createEditable()
	 */
	public function getEditable(): GuiFieldEditable {
		if ($this->editable !== null) {
			return $this->editable;
		}
		
		throw new IllegalStateException('GuiField read only.');
	}
}
