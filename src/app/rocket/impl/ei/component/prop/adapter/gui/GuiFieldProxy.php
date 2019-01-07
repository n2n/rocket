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
namespace rocket\impl\ei\component\prop\adapter\gui;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\GuiField;
use rocket\ei\manage\gui\GuiFieldEditable;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\ui\DisplayItem;
use n2n\web\dispatch\mag\Mag;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\GuiFieldDisplayable;

class GuiFieldProxy implements GuiField, GuiFieldDisplayable, GuiFieldEditable {
	private $eiu;
	private $statelessGuiFieldDisplayable;
	private $statelessGuiFieldEditable;
	
	private $mag;
	
	/**
	 * @param StatelessGuiFieldDisplayable $statelessGuiFieldDisplayable
	 * @param Eiu $eiu
	 */
	public function __construct(Eiu $eiu, StatelessGuiFieldDisplayable $statelessGuiFieldDisplayable, 
			StatelessGuiFieldEditable $statelessGuiFieldEditable = null) {
		$this->eiu = $eiu;
		$this->statelessGuiFieldDisplayable = $statelessGuiFieldDisplayable;
		$this->statelessGuiFieldEditable = $statelessGuiFieldEditable;
	}
	
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::getDisplayable()
	 */
	public function getDisplayable(): GuiFieldDisplayable {
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::getHtmlContainerAttrs()
	 */
	public function getHtmlContainerAttrs(): array {
		return $this->statelessGuiFieldDisplayable->getHtmlContainerAttrs($this->eiu);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::getDisplayItemType()
	 */
	public function getDisplayItemType(): string {
		$displayItemType = $this->statelessGuiFieldDisplayable->getDisplayItemType($this->eiu);
		ArgUtils::valEnum($displayItemType, DisplayItem::getTypes());
		return $displayItemType;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::createUiComponent()
	 */
	public function createUiComponent(HtmlView $view) {
		return $this->statelessGuiFieldDisplayable->createUiComponent($view, $this->eiu);
	}
	
	public function getMessages(): array {
		return $this->eiu->field()->getMessages();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::isReadOnly()
	 */
	public function isReadOnly(): bool {
		return $this->statelessGuiFieldEditable === null
				|| $this->statelessGuiFieldEditable->isReadOnly($this->eiu);
	}
	
	/**
	 * @return bool
	 */
	public function isMandatory(): bool {
		if ($this->statelessGuiFieldEditable === null) {
			return false;
		}
		
		return $this->statelessGuiFieldEditable->isMandatory($this->eiu);
	}

	public function getEditable(): GuiFieldEditable {
		if ($this->statelessGuiFieldEditable === null) {
			throw new IllegalStateException();
		}
		
		return $this;
	}
	
	public function getMag(): Mag {
		if ($this->mag !== null) {
			throw new IllegalStateException('Mag already created.');
		}
		
		$mag = $this->statelessGuiFieldEditable->createMag($this->eiu);
		ArgUtils::valTypeReturn($mag, Mag::class, $this->statelessGuiFieldEditable, 'createMag');
		$this->statelessGuiFieldEditable->loadMagValue($this->eiu, $mag);
		return $this->mag = $mag;
	}
	
	public function save() {
		if ($this->mag === null) {
			throw new IllegalStateException('No mag created.');
		}
		
		$this->statelessGuiFieldEditable->saveMagValue($this->mag, $this->eiu);
	}
}
