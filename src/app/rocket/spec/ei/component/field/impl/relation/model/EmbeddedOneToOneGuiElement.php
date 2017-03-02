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

use rocket\spec\ei\manage\gui\GuiElement;
use rocket\spec\ei\manage\gui\Editable;
use rocket\spec\ei\manage\EiFrame;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\util\ex\IllegalStateException;

class EmbeddedOneToOneGuiElement implements GuiElement {
	private $label;
	private $readOnly;
	private $mandatory;
	private $toOneMappable;
	private $targetEiFrame;
	private $editable;

	private $selectPathExt;
	private $newMappingFormPathExt;

	public function __construct(string $label, ToOneMappable $toOneMappable, EiFrame $targetEiFrame,
			Editable $editable = null) {
		$this->label = $label;
		$this->toOneMappable = $toOneMappable;
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
		return array('class' => 'rocket-control-group');
	}

// 	public function createOutputUiComponent(HtmlView $view) {
// 		$eiFrame = $eiu->frame()->getEiFrame();
// 		$eiMapping = $eiu->entry()->getEiMapping();
// 		$targetEiSelection = $this->createTargetEiSelection($eiFrame, $eiMapping);

// 		if ($targetEiSelection === null) return null;

// 		$eiSelection = $eiMapping->getEiSelection();
// 		$target = $this->eiFieldRelation->getTarget();
// 		$targetEiFrame = $this->eiFieldRelation->createTargetPseudoEiFrame(
// 				$eiFrame, $eiSelection, false);
// 		$targetUtils = new EiuFrame($targetEiFrame);

// 		$targetEiMapping = $targetUtils->createEiMapping($targetEiSelection);

// 		$entryInfo = $targetUtils->createEntryInfo($targetEiMapping);
// 		$view = $entryInfo->getEiMask()->createDetailView($targetEiFrame, $entryInfo);

// 		return $view->getImport($view);
// 	}
	
	public function createOutputUiComponent(HtmlView $view) {
		$targetRelationEntry = $this->toOneMappable->getValue();
		if ($targetRelationEntry === null) return null;
	
		$targetUtils = new EiuFrame($this->targetEiFrame);
		
		return $targetUtils->createDetailView($targetRelationEntry->toEiMapping($targetUtils));
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
