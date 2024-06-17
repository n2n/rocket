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
namespace rocket\ui\si\control\impl;

use rocket\ui\si\control\SiControl;
use rocket\ui\si\control\SiButton;
use rocket\ui\si\control\SiCallResponse;
use n2n\core\container\N2nContext;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;

class CallbackSiControl implements SiControl {

	public function __construct(private \Closure $closure, private SiButton $button, private bool $inputHandled = false) {
	}

	function setInputHandled(bool $inputHandled): static {
		$this->inputHandled = $inputHandled;
		return $this;
	}

	function isInputHandled(): bool {
		return $this->inputHandled;
	}

	public function getType(): string {
		return 'api-call';
	}

	public function getData(): array {
		return [ /*'apiUrl' => $this->apiUrl, 'apiCallId' => $this->apiCallId,*/ 'button' => $this->button, 'inputHandled' => $this->inputHandled ];
	}

	function handleCall(N2nContext $n2nContext): SiCallResponse {
		$mmi = new MagicMethodInvoker($n2nContext);
		$mmi->setClosure($this->closure);
		$mmi->setReturnTypeConstraint(TypeConstraints::namedType(SiCallResponse::class, false));
		return $mmi->invoke();
	}
}