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
use rocket\op\ei\util\Eiu;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;
use Closure;
use rocket\ui\gui\control\GuiControl;
use n2n\web\http\controller\Controller;

class CallbackEiCmdNature extends EiCmdNatureAdapter {

	private Closure|Controller|null $controllerCallback = null;
	private array $entryCallbacks = [];
	private array $selectionCallbacks = [];
	private array $generalCallbacks = [];

	function setController(Closure|Controller|null $controllerCallback): static {
		$this->controllerCallback = $controllerCallback;
		return $this;
	}

	public function lookupController(Eiu $eiu): ?Controller {
		if ($this->controllerCallback === null) {
			return null;
		}

		if (!is_callable($this->controllerCallback)) {
			return $this->controllerCallback;
		}

		$mmi = new MagicMethodInvoker($eiu->getN2nContext());
		$mmi->setClassParamObject(Eiu::class, $eiu);
		$mmi->setReturnTypeConstraint(TypeConstraints::namedType(Controller::class, true));
		$mmi->setClosure($this->controllerCallback);
		return $mmi->invoke();
	}

	function putGeneralGuiControl(string $name, Closure|GuiControl $callback): static {
		$this->generalCallbacks[$name] = $callback;
		return $this;
	}

	function putEntryGuiControl(string $name, Closure|GuiControl $callback): static {
		$this->entryCallbacks[$name] = $callback;
		return $this;
	}

	function putSelectionGuiControl(string $name, Closure|GuiControl $callback): static {
		$this->selectionCallbacks[$name] = $callback;
		return $this;
	}

	/**
	 * @param array $callbacks
	 * @param Eiu $eiu
	 * @return GuiControl[]
	 */
	private function createGuiControls(array $callbacks, Eiu $eiu): array {
		$guiControls = [];

		foreach ($callbacks as $key => $callback) {
			if ($callback instanceof GuiControl) {
				$guiControls[$key] = $callback;
				continue;
			}

			$invoker = new MagicMethodInvoker($eiu->getN2nContext());
			$invoker->setClosure($callback);
			$invoker->setClassParamObject(Eiu::class, $eiu);
			$invoker->setReturnTypeConstraint(TypeConstraints::namedType(GuiControl::class, true));

			if (null !== ($guiControl = $invoker->invoke())) {
				$guiControls[$key] = $guiControl;
			}
		}

		return $guiControls;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\command\EiCmdNature::createSelectionGuiControls()
	 */
	public function createSelectionGuiControls(Eiu $eiu): array {
		return $this->createGuiControls($this->selectionCallbacks, $eiu);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\command\EiCmdNature::createEntryGuiControls()
	 */
	public function createEntryGuiControls(Eiu $eiu): array {
		return $this->createGuiControls($this->entryCallbacks, $eiu);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\command\EiCmdNature::createOverallControls()
	 */
	public function createGeneralGuiControls(Eiu $eiu): array {
		return $this->createGuiControls($this->generalCallbacks, $eiu);
	}

}