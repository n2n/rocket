<?php

namespace rocket\ui\si;

use rocket\ui\si\content\SiGui;
use JsonSerializable;
use n2n\util\type\ArgUtils;
use rocket\ui\si\meta\SiBreadcrumb;
use rocket\ui\si\control\SiControl;
use rocket\ui\si\content\SiZoneCall;
use rocket\op\ei\manage\api\SiCallResult;
use rocket\ui\si\input\CorruptedSiInputDataException;
use rocket\ui\si\input\SiInputResult;

class SiZone implements JsonSerializable {

	function __construct(private SiGui $gui, private ?string $title, private array $breadcrumbs, private array $controls = []) {
		ArgUtils::valArray($this->breadcrumbs, SiBreadcrumb::class);
		ArgUtils::valArray($this->controls, SiControl::class);
	}

	/**
	 * @throws CorruptedSiInputDataException
	 */
	function handleSiZoneCall(SiZoneCall $siZoneCall): SiCallResult {
		$siInputResult = null;

		if (null !== ($siInput = $siZoneCall->getInput())) {
			if (null !== ($siInputError = $this->gui->handleSiInput($siInput))) {
				return SiCallResult::fromInputError($siInputError);
			}

			$siInputResult = new SiInputResult($this->gui->getInputSiValueBoundaries());
		}

		$controlName = $siZoneCall->getControlName();
		if (!isset($this->controls[$controlName])) {
			throw new CorruptedSiInputDataException('Could not find SiControl with name: '
					. $controlName);
		}

		return SiCallResult::fromCallResponse(
				$this->controls[$controlName]->handleCall(),
				$siInputResult);
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