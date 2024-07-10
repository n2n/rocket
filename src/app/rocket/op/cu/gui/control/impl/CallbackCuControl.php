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
namespace rocket\op\cu\gui\control\impl;

use rocket\ui\si\control\SiControl;
use rocket\ui\si\control\impl\CallbackSiControl;
use rocket\ui\si\control\SiButton;
use n2n\util\uri\Url;
use rocket\op\cu\gui\control\CuControl;
use rocket\op\cu\gui\control\CuControlCallId;
use rocket\ui\si\api\response\SiCallResponse;
use rocket\op\util\OpfControlResponse;
use rocket\op\ei\manage\ManageState;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;
use rocket\op\cu\util\Cuu;

class CallbackCuControl implements CuControl {
	private bool $inputHandled = false;

	function __construct(private string $id, private \Closure $callback, private SiButton $siButton) {

	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\gui\control\GuiControl::getId()
	 */
	function getId(): string {
		return $this->id;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\gui\control\GuiControl::isInputHandled()
	 */
	function isInputHandled(): bool {
		return $this->inputHandled;
	}

	function setInputHandled(bool $inputHandled): static {
		$this->inputHandled = $inputHandled;
		return $this;
	}
	

	function toSiControl(Url $apiUrl, CuControlCallId $cuControlCallId): SiControl {
		return new CallbackSiControl($apiUrl, $cuControlCallId, $this->siButton, $this->inputHandled);
	}

	function handle(Cuu $cuu): SiCallResponse {
		$invoker = new MagicMethodInvoker($cuu->getN2nContext());
		$invoker->setClassParamObject(Cuu::class, $cuu);
		$invoker->setReturnTypeConstraint(TypeConstraints::namedType(OpfControlResponse::class, true));
		$invoker->setClosure($this->callback);
		$sifControlResponse = $invoker->invoke();

		if ($sifControlResponse === null) {
			$sifControlResponse = $cuu->f()->newControlResponse();
		}

		return $sifControlResponse->toSiCallResponse($cuu->lookup(ManageState::class)->getEiLifecycleMonitor());
	}

}
