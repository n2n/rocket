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

use rocket\spec\ei\manage\gui\GuiField;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\gui\GuiFieldEditable;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\manage\gui\ui\DisplayItem;
use n2n\impl\web\ui\view\html\HtmlElement;

class EmbeddedOneToManyGuiField implements GuiField {
	private $label;
	private $reduced;
	private $readOnly;
	private $mandatory;
	private $toManyEiField;
	private $targetEiFrame;
	private $compact;
	private $editable;

	private $selectPathExt;
	private $newMappingFormPathExt;

	public function __construct(string $label, bool $reduced, ToManyEiField $toManyEiField, EiFrame $targetEiFrame,
			bool $compact, GuiFieldEditable $editable = null) {
		$this->label = $label;
		$this->reduced = $reduced;
		$this->toManyEiField = $toManyEiField;
		$this->targetEiFrame = $targetEiFrame;
		$this->compact = $compact;
		$this->editable = $editable;
	}

	public function isReadOnly(): bool {
		return $this->editable === null;
	}
	
	/**
	 * @return bool
	 */
	public function isReduced() {
		return $this->reduced;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\Displayable::getDisplayItemType()
	 */
	public function getDisplayItemType() {
		return DisplayItem::TYPE_SIMPLE_GROUP;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\Displayable::getUiOutputLabel()
	 */
	public function getUiOutputLabel(): string {
		return $this->label;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\Displayable::getOutputHtmlContainerAttrs()
	 */
	public function getOutputHtmlContainerAttrs(): array {
		return array();
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\Displayable::createOutputUiComponent()
	 */
	public function createOutputUiComponent(HtmlView $view) {
		if ($this->compact) {
			return $this->createCompactOutputUiComponent($view);
		}
		
		$targetRelationEntries = $this->toManyEiField->getValue();
		if (empty($targetRelationEntries)) return null;
		
		$targetEiuFrame = new EiuFrame($this->targetEiFrame);
		
		$targetEiuEntries = array();
		foreach ($targetRelationEntries as $targetRelationEntry) {
			$targetEiEntry = null;
			if ($targetRelationEntry->hasEiEntry()) {
				$targetEiEntry = $targetRelationEntry->getEiEntry();
			} else {
				$targetEiEntry = $targetEiuFrame->createEiEntry(
						$targetRelationEntry->getEiObject());
			}
			
			$targetEiuEntries[] = $targetEiuFrame->entry($targetEiEntry);
		}

		return $view->getImport('\rocket\impl\ei\component\prop\relation\view\embeddedOneToMany.html',
				array('eiuEntries' => $targetEiuEntries, 'reduced' => $this->reduced));
	}

	/**
	 * @param HtmlView $view
	 * @return NULL|\n2n\impl\web\ui\view\html\HtmlElement
	 */
	private function createCompactOutputUiComponent(HtmlView $view) {
		$targetRelationEntries = $this->toManyEiField->getValue();
		if (empty($targetRelationEntries)) return null;
		
		$targetEiuFrame = new EiuFrame($this->targetEiFrame);
		$htmlElem = new HtmlElement('ul', array('class' => 'list-unstyled'), '');
		
		foreach ($targetRelationEntries as $targetRelationEntry) {
			$iconType = $targetEiuFrame->getGenericIconType($targetRelationEntry->getEiObject());
			$label = $targetEiuFrame->getGenericLabel($targetRelationEntry->getEiObject());
			$htmlElem->appendLn(new HtmlElement('li', null, array(
					new HtmlElement('i', array('class' => 'fa fa-' . $iconType), ''),
					new HtmlElement('span', null, $label))));
		}
		
		return $htmlElem;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::createEditable()
	 */
	public function getEditable(): GuiFieldEditable {
		if ($this->editable !== null) {
			return $this->editable;
		}
		
		throw new IllegalStateException('GuiField read only.');
	}
}
