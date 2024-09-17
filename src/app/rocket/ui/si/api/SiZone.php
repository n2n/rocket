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

namespace rocket\ui\si\api;

use rocket\ui\si\content\SiGui;
use JsonSerializable;
use n2n\util\type\ArgUtils;
use rocket\ui\si\meta\SiBreadcrumb;
use rocket\ui\si\control\SiControl;
use rocket\ui\si\api\request\SiZoneCall;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\core\container\N2nContext;
use rocket\ui\si\api\response\SiCallResponse;
use rocket\ui\si\SiPayloadFactory;
use rocket\ui\si\api\response\SiApiCallResponse;
use rocket\ui\si\api\response\SiZoneCallResponse;

class SiZone {

	function __construct(private SiGui $gui, private ?string $title, private array $breadcrumbs, private array $controls = []) {
		ArgUtils::valArray($this->breadcrumbs, SiBreadcrumb::class);
		ArgUtils::valArray($this->controls, SiControl::class);
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	function handleSiZoneCall(SiZoneCall $siZoneCall, N2nContext $n2nContext): SiZoneCallResponse {

		$zoneCallResponse = new SiZoneCallResponse();
		$siInputResult = null;

		if (null !== ($siInput = $siZoneCall->getInput())) {
			$siInputResult = $this->gui->handleSiInput($siInput, $n2nContext);
			$zoneCallResponse->setInputResult($siInputResult);
			if (!$siInputResult->isValid()) {
				return $zoneCallResponse;
			}
		}

		$controlName = $siZoneCall->getZoneControlName();
		if (!isset($this->controls[$controlName])) {
			throw new CorruptedSiDataException('Could not find SiControl with name: '
					. $controlName);
		}

		$zoneCallResponse->setCallResponse($this->controls[$controlName]->handleCall($n2nContext));
		return $zoneCallResponse;
	}

	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			'title' => $this->title,
			'breadcrumbs' => $this->breadcrumbs,
			'gui' => SiPayloadFactory::buildDataFromComp($this->gui, $n2nContext),
			'controls' => SiPayloadFactory::createDataFromControls($this->controls)
		];
	}
}