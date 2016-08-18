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
use n2n\ui\view\impl\html\HtmlView;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\gui\Editable;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\util\model\EiStateUtils;
use n2n\ui\view\impl\html\HtmlElement;

class EmbeddedOneToManyGuiElement implements GuiElement {
	private $label;
	private $readOnly;
	private $mandatory;
	private $toManyMappable;
	private $targetEiState;
	private $editable;

	private $selectPathExt;
	private $newMappingFormPathExt;

	public function __construct(string $label, ToManyMappable $toManyMappable, EiState $targetEiState,
			Editable $editable = null) {
		$this->label = $label;
		$this->toManyMappable = $toManyMappable;
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
		$targetRelationEntries = $this->toManyMappable->getValue();
		if (empty($targetRelationEntries)) return null;
		
		$targetEiStateUtils = new EiStateUtils($this->targetEiState);
		
		$detailViews = array();
		foreach ($targetRelationEntries as $targetRelationEntry) {
			$targetEiMapping = null;
			if ($targetRelationEntry->hasEiMapping()) {
				$targetEiMapping = $targetRelationEntry->getEiMapping();
			} else {
				$targetEiMapping = $targetEiStateUtils->createEiMapping(
						$targetRelationEntry->getEiSelection());
			}
			
			if ($targetEiMapping->isAccessible()) {
				$detailViews[] = $targetEiStateUtils->createDetailView($targetEiMapping);
			} else {
				$detailViews[] = new HtmlElement('div', array('rocket-inaccessible'), 
						$targetEiStateUtils->createIdentityString($targetEiMapping->getEiSelection()));
			}
		}

		return $view->getImport('\rocket\spec\ei\component\field\impl\relation\view\embeddedOneToMany.html',
				array('detailViews' => $detailViews));
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
