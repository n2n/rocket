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
namespace rocket\impl\ei\manage\gui;

use rocket\op\ei\manage\entry\EiEntry;
use rocket\ui\si\control\SiControl;
use rocket\ui\si\api\response\SiCallResponse;
use rocket\ui\si\control\impl\CallbackSiControl;
use rocket\ui\si\control\SiButton;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\ManageState;
use rocket\ui\gui\EiGuiDeclaration;
use rocket\op\ei\manage\frame\EiFrame;
use n2n\util\type\ArgUtils;
use rocket\ui\gui\control\GuiControl;
use rocket\op\util\OpfControlResponse;
use n2n\util\ex\NotYetImplementedException;
use rocket\ui\gui\control\GuiControlMap;
use rocket\ui\gui\GuiCallResponse;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\core\container\N2nContext;
use rocket\op\ei\util\EiuAnalyst;

class CallbackGuiControl implements GuiControl {
	private $inputHandled = false;

	function __construct(private \Closure $callback, private SiButton $siButton) {
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
	
	function getChildById(string $id): ?GuiControl {
		return null;
	}

	function getSiControl(): SiControl {
		return new CallbackSiControl(fn (N2nContext $c) => $this->execCall($c), $this->siButton, $this->inputHandled);
	}
	
	/**
	 * @param Eiu $eiu
	 * @return SiCallResponse
	 */
	private function execCall(N2nContext $n2nContext) {
		$sifControlResponse = $this->callback->__invoke();
		ArgUtils::valTypeReturn($sifControlResponse, GuiCallResponse::class, null, $this->callback, true);
		
// 		$mmi = new MagicMethodInvoker($eiu->getN2nContext());
// 		$mmi->setMethod(new \ReflectionFunction($this->callback));
// 		$mmi->setClassParamObject(Eiu::class, $eiu);
// 		$mmi->setClassParamObject($className, $obj)
// 		$mmi->setReturnTypeConstraint(TypeConstraints::type(RfControlResponse::class, true));
		
// 		$eiuControlResponse = $mmi->invoke();
		if ($sifControlResponse === null) {
			$sifControlResponse = new OpfControlResponse(EiuAnalyst::fromEiArgs($n2nContext));
		}
		
		return $sifControlResponse->toSiCallResponse();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\gui\control\GuiControl::handleCall()
	 */
	function handleCall(): SiCallResponse {
		return $this->execCall();
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\gui\control\GuiControl::handleEntry()
	 */
	function handleEntry(EiFrame $eiFrame, EiGuiDeclaration $eiGuiDeclaration, EiEntry $eiEntry): SiCallResponse {
		return $this->execCall(new Eiu($eiFrame, $eiGuiDeclaration, $eiEntry), null);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\gui\control\GuiControl::handleEntries()
	 */
	function handleEntries(EiFrame $eiFrame, EiGuiDeclaration $eiGuiDeclaration, array $eiEntries): SiCallResponse {
		throw new NotYetImplementedException();
	}

	function getForkGuiControlMap(): ?GuiControlMap {
		return null;
	}
}
