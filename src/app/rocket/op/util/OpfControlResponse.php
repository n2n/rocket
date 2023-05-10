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
namespace rocket\op\util;

use rocket\si\control\SiCallResponse;
use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\EiType;
use n2n\l10n\Message;
use n2n\util\uri\Url;
use rocket\op\ei\manage\veto\EiLifecycleMonitor;
use rocket\op\ei\manage\EiObject;
use rocket\si\control\SiNavPoint;

class OpfControlResponse {
	private $eiuAnalyst;
	/**
	 * @var SiCallResponse
	 */
	private $siCallResponse;
	/**
	 * @var bool
	 */
	private $noAutoEvents = false;
	
	/**
	 * @var EiObject
	 */
	private $pendingHighlightEiObjects = [];
	
	/**
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(EiuAnalyst $eiuAnalyst) {
		$this->eiuAnalyst = $eiuAnalyst;
		$this->siCallResponse = new SiCallResponse();
	}
	
	/**
	 * @return OpfControlResponse
	 */
	function redirectBack(): static {
		$this->siCallResponse->setDirective(SiCallResponse::DIRECTIVE_REDIRECT_BACK);
		
		$eiFrame = $this->eiuAnalyst->getEiFrame(false);
		
		if (null !== ($overviewNavPoint = $eiFrame?->getOverviewNavPoint(false))) {
			$this->siCallResponse->setNavPoint($overviewNavPoint);
		}
		
		return $this;
	}
	
	/**
	 * @param Url $url
	 * @return OpfControlResponse
	 */
	function redirectBackOrRef(Url $url): static {
		$this->siCallResponse->setDirective(SiCallResponse::DIRECTIVE_REDIRECT_BACK);
		$this->siCallResponse->setNavPoint(SiNavPoint::siref($url));
		return $this;
	}
	
	/**
	 * @param Url $url
	 * @return OpfControlResponse
	 */
	function redirectBackOrHref(Url $url): static {
		$this->siCallResponse->setDirective(SiCallResponse::DIRECTIVE_REDIRECT_BACK);
		$this->siCallResponse->setNavPoint(SiNavPoint::href($url));
		return $this;
	}
	
	/**
	 * @param Url $url
	 * @return OpfControlResponse
	 */
	function redirectToRef(Url $url): static {
		$this->siCallResponse->setDirective(SiCallResponse::DIRECTIVE_REDIRECT);
		$this->siCallResponse->setNavPoint(SiNavPoint::siref($url));
		return $this;
	}

	/**
	 * @param Url|string $url
	 * @return OpfControlResponse
	 */
	function redirectToHref(Url|string $url): static {
		$url = Url::create($url);
		$this->siCallResponse->setDirective(SiCallResponse::DIRECTIVE_REDIRECT);
		$this->siCallResponse->setNavPoint(SiNavPoint::href($url));
		return $this;
	}
	
	/**
	 * @param Message|string $message
	 * @return OpfControlResponse
	 */
	function message($message) {
		$this->siCallResponse->addMessage(Message::create($message),
				$this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
		return $this;
	}
	
// 	/**
// 	 * @param mixed ...$eiTypeArgs
// 	 * @return RfControlResponse
// 	 */
// 	public function eiTypeChanged(...$eiTypeArgs) {
// 		foreach ($eiTypeArgs as $eiTypeArg) {
// 			$this->groupChanged(self::buildTypeId(EiuAnalyst::buildEiTypeFromEiArg($eiTypeArg)));
// 		}
// 		return $this;
// 	}

	/**
	 * @param mixed ...$eiObjectArgs
	 * @return OpfControlResponse
	 */
	function highlight(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg',
					$this->eiuAnalyst->getSpec(false), true);
			
			if (!$eiObject->getEiEntityObj()->hasId()) {
				$this->pendingHighlightEiObjects[] = $eiObject;
				continue;
			}
			
			$this->siCallResponse->addHighlight(
					self::buildCategory($eiObject->getEiEntityObj()->getEiType()), 
					$eiObject->getEiEntityObj()->getPid());
		}
		
		return $this;
	}
	
	/**
	 * @param bool $noAutoEvents
	 * @return OpfControlResponse
	 */
	function noAutoEvents(bool $noAutoEvents = true) {
		$this->noAutoEvents = true;
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return OpfControlResponse
	 */
	function entryAdded(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, SiCallResponse::EVENT_TYPE_ADDED);
		}
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return OpfControlResponse
	 */
	function entryChanged(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, SiCallResponse::EVENT_TYPE_CHANGED);
		}
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return OpfControlResponse
	 */
	function entryRemoved(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, SiCallResponse::EVENT_TYPE_REMOVED);
		}
		return $this;
	}
	
	private function eiObjectMod($eiObjectArg, string $modType) {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', null, true);
		
		$category = self::buildCategory($eiObject->getEiEntityObj()->getEiType());
		
		$pid = null;
		if ($eiObject->getEiEntityObj()->hasId()) {
			$pid = $eiObject->getEiEntityObj()->getPid();
		}
		
		$this->siCallResponse->addEvent($eiObject->createSiEntryIdentifier(), $modType);
	}
	
	/**
	 * @param EiType $eiType
	 * @return string
	 */
	private static function buildCategory(EiType $eiType) {
		return $eiType->getSupremeEiType()->getId();
	}
	
	/**
	 * @param EiLifecycleMonitor $elm
	 * @return SiCallResponse
	 */
	function toSiCallResponse(EiLifecycleMonitor $elm): SiCallResponse {
		if ($this->noAutoEvents) {
			return $this->siCallResponse;
		}
		
		$taa = $elm->approve();
		
		if (!$taa->isSuccessful()) {
			$this->message(...$taa->getReasonMessages());
			return $this->siCallResponse;
		}
		
		foreach ($this->pendingHighlightEiObjects as $eiObject) {
			$this->highlight($eiObject);
		}
		$this->pendingHighlightEiObjects = [];
		
		foreach ($elm->getUpdateActions() as $action) {
			$this->eiObjectMod($action->getEiObject(), SiCallResponse::EVENT_TYPE_CHANGED);
			$this->highlight($action->getEiObject());
		}
		
		foreach ($elm->getPersistActions() as $action) {
			$this->eiObjectMod($action->getEiObject(), SiCallResponse::EVENT_TYPE_ADDED);
			$this->highlight($action->getEiObject());
		}
		
		foreach ($elm->getRemoveActions() as $action) {
			$this->eiObjectMod($action->getEiObject(), SiCallResponse::EVENT_TYPE_REMOVED);
		}
		
		return $this->siCallResponse;
	}
}