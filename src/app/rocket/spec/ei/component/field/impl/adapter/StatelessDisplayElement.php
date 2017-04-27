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
namespace rocket\spec\ei\component\field\impl\adapter;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\gui\GuiElement;
use rocket\spec\ei\manage\gui\Editable;
use rocket\spec\ei\manage\util\model\Eiu;

class StatelessDisplayElement implements GuiElement {
	private $statelessDisplayable;
	private $eiu;
	
	/**
	 * @param StatelessDisplayable $statelessDisplayable
	 * @param Eiu $eiu
	 */
	public function __construct(StatelessDisplayable $statelessDisplayable, Eiu $eiu) {
		$this->statelessDisplayable = $statelessDisplayable;
		$this->eiu = $eiu;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\Displayable::getUiOutputLabel()
	 */
	public function getUiOutputLabel(): string {
		return $this->statelessDisplayable->getUiOutputLabel($this->eiu);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\Displayable::getOutputHtmlContainerAttrs()
	 */
	public function getOutputHtmlContainerAttrs(): array {
		return $this->statelessDisplayable->getOutputHtmlContainerAttrs($this->eiu);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\Displayable::createOutputUiComponent()
	 */
	public function createOutputUiComponent(HtmlView $view) {
		return $this->statelessDisplayable->createOutputUiComponent($view, $this->eiu);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiElement::isReadOnly()
	 */
	public function isReadOnly(): bool {
		return true;
	}
	
	/**
	 * @return bool
	 */
	public function isMandatory(): bool {
		return false;
	}

	public function getEditable(): Editable {
		throw new IllegalStateException();
	}
}
