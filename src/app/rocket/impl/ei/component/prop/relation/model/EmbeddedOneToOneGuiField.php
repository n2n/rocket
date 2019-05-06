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
use rocket\ei\manage\gui\GuiFieldEditable;
use rocket\ei\manage\frame\EiFrame;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\ui\DisplayItem;
use n2n\impl\web\ui\view\html\HtmlElement;
use rocket\ei\util\Eiu;
use n2n\l10n\N2nLocale;
use n2n\l10n\Lstr;

class EmbeddedOneToOneGuiField implements GuiField, GuiFieldDisplayable {
	private $labelLstr;
	private $reduced;
	private $readOnly;
	private $mandatory;
	private $toOneEiField;
	private $targetEiFrame;
	private $compact;
	private $editable;

	private $selectPathExt;
	private $newMappingFormPathExt;

	public function __construct(Lstr $labelLstr, bool $reduced, ToOneEiField $toOneEiField, EiFrame $targetEiFrame,
			bool $compact, GuiFieldEditable $editable = null) {
		$this->labelLstr = $labelLstr;
		$this->reduced = $reduced;
		$this->toOneEiField = $toOneEiField;
		$this->targetEiFrame = $targetEiFrame;
		$this->compact = $compact;
		$this->editable = $editable;
	}

	/**
	 * @return bool
	 */
	public function isReduced() {
		return $this->reduced;
	}
	
	public function isReadOnly(): bool {
		return $this->editable === null;
	}

	public function getDisplayItemType(): string {
		return DisplayItem::TYPE_SIMPLE_GROUP;
	}
	
	/**
	 * @return string
	 */
	public function getUiOutputLabel(N2nLocale $n2nLocale): string {
		return $this->labelLstr->t($n2nLocale);
	}

	/**
	 * @return array
	 */
	public function getHtmlContainerAttrs(): array {
		return array();
	}
	
	public function createUiComponent(HtmlView $view) {
		$targetRelationEntry = $this->toOneEiField->getValue();
		if ($targetRelationEntry === null) return null;
		
		$targetUtils = (new Eiu($this->targetEiFrame))->frame();
		
		if ($this->compact) {
			$iconType = $targetUtils->getGenericIconType($targetRelationEntry->getEiObject());
			$label  = $targetUtils->getGenericLabel($targetRelationEntry->getEiObject());
			return new HtmlElement('span', null, array(
					new HtmlElement('i', array('class' => $iconType), ''),
					PHP_EOL,
					new HtmlElement('span', null, $label)));
		}
		
		if (!$this->reduced) {
			$eiuEntry = $targetUtils->entry($targetRelationEntry->toEiEntry($targetUtils));
			return $eiuEntry->newEntryGui()/*->allowControls()
					->addDisplayContainer(DisplayItem::TYPE_LIGHT_GROUP, $eiuEntry->getGenericLabel())*/
					->createView($view);
		}
		
		return $view->getImport('\rocket\impl\ei\component\prop\relation\view\embeddedOneToOne.html',
				array('eiuEntry' => $targetUtils->entry($targetRelationEntry->toEiEntry($targetUtils)), 
						'reduced' => $this->reduced));
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
