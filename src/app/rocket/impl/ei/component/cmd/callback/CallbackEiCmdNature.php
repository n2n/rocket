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
namespace rocket\impl\ei\component\cmd\callback;

use rocket\impl\ei\component\cmd\adapter\EiCmdNatureAdapter;
use rocket\ei\util\Eiu;
use rocket\si\control\SiButton;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;
use Closure;
use rocket\ei\manage\gui\control\GuiControl;

class CallbackEiCmdNature extends EiCmdNatureAdapter {
	private array $entryGuiControlDefs = [];
	private array $selectionGuiControlDefs = [];
	private array $generalGuiControlDefs = [];

	private int $idCounter = 0;

	private function makeId(?string $id): string {
		return $id ?? 'callback-' . $this->idCounter++;
	}

	/**
	 * @param SiButton|Closure $siButton
	 * @param Closure $callback
	 * @param string|null $id
	 * @return $this
	 */
	function addGeneralGuiControl(SiButton|Closure $siButton, Closure $callback, string $id = null): static {
		$this->generalGuiControlDefs[$this->makeId($id)] = new GuiControlDef($siButton, $callback);
		return $this;
	}

	/**
	 * @param SiButton|Closure $siButton
	 * @param Closure $callback
	 * @param string|null $id
	 * @return $this
	 */
	function addEntryGuiControl(SiButton|Closure $siButton, Closure $callback, string $id = null): static {
		$this->entryGuiControlDefs[$this->makeId($id)] = new GuiControlDef($siButton, $callback);
		return $this;
	}

	/**
	 * @param SiButton|Closure $siButton
	 * @param Closure $callback
	 * @param string|null $id
	 * @return $this
	 */
	function addSelectionGuiControl(SiButton|Closure $siButton, Closure $callback, string $id = null): static {
		$this->selectionGuiControlDefs[$this->makeId($id)] = new GuiControlDef($siButton, $callback);
		return $this;
	}

	/**
	 * @param GuiControlDef[] $defs
	 * @param Eiu $eiu
	 * @return GuiControl[]
	 */
	private function createGuiControls(array $defs, Eiu $eiu) {
		$guiControls = [];

		foreach ($defs as $def) {
			$siButton = $def->buildSiButton($eiu);
			if ($siButton === null) {
				continue;
			}

			$guiControls[] = $eiu->factory()->controls()->newCallback($siButton, $def->getCallback());
		}

		return $guiControls;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\EiCmdNature::createSelectionGuiControls()
	 */
	public function createSelectionGuiControls(Eiu $eiu): array {
		return $this->createGuiControls($this->selectionGuiControlDefs, $eiu);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\EiCmdNature::createEntryGuiControls()
	 */
	public function createEntryGuiControls(Eiu $eiu): array {
		return $this->createGuiControls($this->entryGuiControlDefs, $eiu);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\EiCmdNature::createOverallControls()
	 */
	public function createGeneralGuiControls(Eiu $eiu): array {
		return $this->createGuiControls($this->generalGuiControlDefs, $eiu);
	}

}


class GuiControlDef {
	function __construct(private SiButton|Closure $siButton, private Closure $callback) {

	}

	function buildSiButton(Eiu $eiu) {
		if ($this->siButton instanceof SiButton) {
			return $this->siButton;
		}

		$invoker = new MagicMethodInvoker($eiu->getN2nContext());
		$invoker->setClassParamObject(Eiu::class, $eiu);
		$invoker->setReturnTypeConstraint(TypeConstraints::namedType(SiButton::class, true));

		return $invoker->invoke();
	}

	function getCallback() {
		return $this->callback;
	}
}