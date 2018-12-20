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

use rocket\ei\manage\gui\GuiField;
use rocket\ei\manage\gui\GuiFieldDisplayable;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\gui\GuiFieldEditable;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\ui\DisplayItem;
use n2n\impl\web\ui\view\html\HtmlElement;
use rocket\ei\util\Eiu;
use n2n\l10n\N2nLocale;
use n2n\l10n\Lstr;

class EmbeddedOneToManyGuiField implements GuiField, GuiFieldDisplayable {
	private $labelLstr;
	private $reduced;
	private $readOnly;
	private $mandatory;
	private $toManyEiField;
	private $targetEiFrame;
	private $compact;
	private $editable;

	private $selectPathExt;
	private $newMappingFormPathExt;

	public function __construct(Lstr $labelLstr, bool $reduced, ToManyEiField $toManyEiField, EiFrame $targetEiFrame,
			bool $compact, GuiFieldEditable $editable = null) {
		$this->labelLstr = $labelLstr;
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
	 * @see \rocket\ei\manage\gui\GuiField::getDisplayItemType()
	 */
	public function getDisplayItemType(): string {
		return DisplayItem::TYPE_SIMPLE_GROUP;
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
		
		$targetRelationEntries = $this->toManyEiField->getValue();
		if (empty($targetRelationEntries)) return null;
		
		$targetEiuFrame = (new Eiu($this->targetEiFrame))->frame();
		
		$targetEiuEntries = array();
		foreach ($targetRelationEntries as $targetRelationEntry) {
			$targetEiEntry = null;
			if ($targetRelationEntry->hasEiEntry()) {
				$targetEiEntry = $targetRelationEntry->getEiEntry();
			} else {
				$targetEiEntry = $targetEiuFrame->entry($targetRelationEntry->getEiObject())->getEiEntry();
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
		
		$targetEiuFrame = (new Eiu($this->targetEiFrame))->frame();
		$htmlElem = new HtmlElement('ul', array('class' => 'list-unstyled'), '');
		
		foreach ($targetRelationEntries as $targetRelationEntry) {
			$iconType = $targetEiuFrame->getGenericIconType($targetRelationEntry->getEiObject());
			$label = $targetEiuFrame->createIdentityString($targetRelationEntry->getEiObject());
			$htmlElem->appendLn(new HtmlElement('li', 
					array('class' => 'list-inline-item rocket-impl-content-type', 'title' => $label),
					array(new HtmlElement('i', array('class' => 'fa fa-' . $iconType), ''))));
		}
		
		return $htmlElem;
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
