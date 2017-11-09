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
namespace rocket\spec\ei\component\field\impl\ci\model;

use rocket\spec\ei\manage\gui\GuiField;
use rocket\spec\ei\manage\gui\Editable;
use rocket\spec\ei\component\field\impl\relation\model\ToManyEiField;
use rocket\spec\ei\manage\EiFrame;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\impl\web\ui\view\html\HtmlElement;
use rocket\spec\ei\component\field\impl\ci\ContentItemsEiProp;
use rocket\spec\ei\manage\gui\ui\DisplayItem;
use n2n\util\ex\IllegalStateException;

class ContentItemGuiField implements GuiField {
	private $label;
	private $panelConfigs;
	private $mandatory;
	private $toManyEiField;
	private $targetEiFrame;
	private $editable;

	private $selectPathExt;
	private $newMappingFormPathExt;

	/**
	 * @param string $label
	 * @param array $panelConfigs
	 * @param ToManyEiField $toManyEiField
	 * @param EiFrame $targetEiFrame
	 * @param Editable $editable
	 */
	public function __construct(string $label, array $panelConfigs, ToManyEiField $toManyEiField, EiFrame $targetEiFrame,
			Editable $editable = null) {
		$this->label = $label;
		$this->panelConfigs = $panelConfigs;
		$this->toManyEiField = $toManyEiField;
		$this->targetEiFrame = $targetEiFrame;
		$this->editable = $editable;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::isReadOnly()
	 */
	public function isReadOnly(): bool {
		return $this->editable === null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\Displayable::getGroupType()
	 */
	public function getGroupType() {
		return DisplayItem::TYPE_SIMPLE;
	}
	
	/**
	 * @return PanelConfig[] 
	 */
	public function getPanelConfigs() {
		return $this->panelConfigs;
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
		$targetUtils = new EiuFrame($this->targetEiFrame);
		$panelEiPropPath = ContentItemsEiProp::getPanelEiPropPath();
		
		$groupedUiComponents = array();
		foreach ($this->toManyEiField->getValue() as $targetRelationEntry) {
			$targetEiEntry = null;
			if ($targetRelationEntry->hasEiEntry()) {
				$targetEiEntry = $targetRelationEntry->getEiEntry();
			} else {
				$targetEiEntry = $targetUtils->createEiEntry(
						$targetRelationEntry->getEiObject());
			}
			
			$panelName = (string) $targetEiEntry->getValue($panelEiPropPath, true);
			if (!isset($groupedUiComponents[$panelName])) {
				$groupedUiComponents[$panelName] = array();
			}
			
			if ($targetEiEntry->isAccessible()) {
				$groupedUiComponents[$panelName][] = $targetUtils->newGui(true)->appendNewEntryGui($targetEiEntry)->createView();
			} else {
				$groupedUiComponents[$panelName][] = new HtmlElement('div', array('rocket-inaccessible'), 
						$targetUtils->createIdentityString($targetEiEntry->getEiObject()));
			}
		}
		
		return $view->getImport('\rocket\spec\ei\component\field\impl\ci\view\contentItems.html',
				array('panelConfigs' => $this->panelConfigs, 'groupedUiComponents' => $groupedUiComponents));
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::createEditable()
	 */
	public function getEditable(): Editable {
		if ($this->editable !== null) {
			return $this->editable;
		}
		
		throw new IllegalStateException('GuiField read only.');
	}
}
