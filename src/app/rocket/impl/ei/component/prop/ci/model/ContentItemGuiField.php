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
namespace rocket\impl\ei\component\prop\ci\model;

use rocket\ei\manage\gui\GuiField;
use rocket\ei\manage\gui\GuiFieldDisplayable;
use rocket\ei\manage\gui\GuiFieldEditable;
use rocket\impl\ei\component\prop\relation\model\ToManyEiField;
use rocket\ei\manage\frame\EiFrame;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\impl\ei\component\prop\ci\ContentItemsEiProp;
use rocket\ei\manage\gui\ui\DisplayItem;
use n2n\util\ex\IllegalStateException;
use rocket\ei\util\Eiu;
use n2n\l10n\Lstr;
use n2n\l10n\N2nLocale;
use n2n\impl\web\ui\view\html\HtmlElement;

class ContentItemGuiField implements GuiField, GuiFieldDisplayable {
	private $labelLstr;
	private $panelConfigs;
	private $mandatory;
	private $toManyEiField;
	private $targetEiFrame;
	private $compact;
	private $editable;

	private $selectPathExt;
	private $newMappingFormPathExt;

	/**
	 * @param string $labelLstr
	 * @param array $panelConfigs
	 * @param ToManyEiField $toManyEiField
	 * @param EiFrame $targetEiFrame
	 * @param GuiFieldEditable $editable
	 */
	public function __construct(Lstr $labelLstr, array $panelConfigs, ToManyEiField $toManyEiField, EiFrame $targetEiFrame,
			bool $compact, GuiFieldEditable $editable = null) {
		$this->labelLstr = $labelLstr;
		$this->panelConfigs = $panelConfigs;
		$this->toManyEiField = $toManyEiField;
		$this->targetEiFrame = $targetEiFrame;
		$this->compact = $compact;
		$this->editable = $editable;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::isReadOnly()
	 */
	public function isReadOnly(): bool {
		return $this->editable === null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::getDisplayItemType()
	 */
	public function getDisplayItemType(): string {
		return DisplayItem::TYPE_SIMPLE_GROUP;
	}
	
	/**
	 * @return PanelConfig[] 
	 */
	public function getPanelConfigs() {
		return $this->panelConfigs;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::getUiOutputLabel()
	 */
	public function getUiOutputLabel(N2nLocale $n2nLocale): string {
		return $this->labelLstr->t($n2nLocale);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::getHtmlContainerAttrs()
	 */
	public function getHtmlContainerAttrs(): array {
		return array();
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::createUiComponent()
	 */
	public function createUiComponent(HtmlView $view) {
		if ($this->compact) {
			return $this->createCompactOutputUiComponent($view);
		}
		
		$targetUtils = (new Eiu($this->targetEiFrame))->frame();
		$panelEiPropPath = ContentItemsEiProp::getPanelEiPropPath();
		
		$groupedEiuEntries = array();
		foreach ($this->toManyEiField->getValue() as $targetRelationEntry) {
			$targetEiuEntry = null;
			if ($targetRelationEntry->hasEiEntry()) {
				$targetEiuEntry = $targetUtils->entry($targetRelationEntry->getEiEntry());
			} else {
				$targetEiuEntry = $targetUtils->entry($targetRelationEntry->getEiObject());
			}
			
			$panelName = (string) $targetEiuEntry->getValue($panelEiPropPath, true);
			if (!isset($groupedEiuEntries[$panelName])) {
				$groupedEiuEntries[$panelName] = array();
			}
			
			$groupedEiuEntries[$panelName][] = $targetEiuEntry;
		}
		
		return $view->getImport('\rocket\impl\ei\component\prop\ci\view\contentItems.html',
				array('panelLayout' => new PanelLayout($this->panelConfigs), 'groupedEiuEntries' => $groupedEiuEntries));
	}
	
	public function createCompactOutputUiComponent(HtmlView $view) {
	
		
		$targetUtils = (new Eiu($this->targetEiFrame))->frame();
		$panelEiPropPath = ContentItemsEiProp::getPanelEiPropPath();
		
		$groupedUiComponents = array();
		foreach ($this->toManyEiField->getValue() as $targetRelationEntry) {
			$targetEiEntry = null;
			if ($targetRelationEntry->hasEiEntry()) {
				$targetEiEntry = $targetRelationEntry->getEiEntry();
			} else {
				$targetEiEntry = $targetUtils->entry($targetRelationEntry->getEiObject())->getEiEntry(true);
			}
			
			$panelName = (string) $targetEiEntry->getValue($panelEiPropPath);
			if (!isset($groupedUiComponents[$panelName])) {
				$groupedUiComponents[$panelName] = array();
			}
			
// 			if ($targetEiEntry->isAccessible()) {
				$iconType = $targetUtils->getGenericIconType($targetEiEntry);
				// $label = $targetUtils->getGenericLabel($targetEiEntry);
				$label = $targetUtils->createIdentityString($targetEiEntry->getEiObject());
				$groupedUiComponents[$panelName][] = new HtmlElement('li', array('title' => $label,
						'class' => 'list-inline-item rocket-impl-content-type'), 
						array(new HtmlElement('i', array('class' => $iconType), '')));
// 			} else {
// 				$groupedUiComponents[$panelName][] = new HtmlElement('li', array('rocket-inaccessible'),
// 						$targetUtils->createIdentityString($targetEiEntry->getEiObject()));
// 			}
		}
		
		return $view->getImport('\rocket\impl\ei\component\prop\ci\view\compactContentItems.html',
				array('panelConfigs' => $this->panelConfigs, 'groupedUiComponents' => $groupedUiComponents));
	}
	
	public function getDisplayable(): GuiFieldDisplayable {
		return $this;
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
