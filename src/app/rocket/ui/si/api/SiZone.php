<?php

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

class SiZone implements JsonSerializable {

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
	}

	function jsonSerialize(): mixed {
		return [
			'title' => $this->title,
			'breadcrumbs' => $this->breadcrumbs,
			'gui' => SiPayloadFactory::buildDataFromComp($this->gui),
			'controls' => SiPayloadFactory::createDataFromControls($this->controls)
		];
	}
}