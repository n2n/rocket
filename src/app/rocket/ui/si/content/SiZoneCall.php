<?php

namespace rocket\ui\si\content;

use n2n\web\http\controller\impl\ControllingUtils;
use n2n\web\http\Method;
use rocket\ui\si\input\SiInput;
use rocket\ui\si\input\SiInputFactory;
use rocket\ui\gui\control\GuiControlPath;
use rocket\ui\si\input\CorruptedSiInputDataException;

class SiZoneCall {

	function __construct(private ?SiInput $input, private string $controlName) {

	}

	function getInput(): ?SiInput {
		return $this->input;
	}

	function getControlName(): string {
		return $this->controlName;
	}


	/**
	 * @throws CorruptedSiInputDataException
	 */
	static function fromCu(ControllingUtils $cu): ?SiZoneCall {
		$apiCallIdParam = $cu->getParamPost('zoneControlName');
		if (!($cu->getRequest()->getMethod() === Method::POST && null !== $apiCallIdParam)) {
			return null;
		}

		$siInput = null;
		if (null !== ($entryInputMapsParam = $this->cu->getParamPost('entryInputMaps'))) {
			$siInput = (new SiInputFactory())->create($entryInputMapsParam->parseJson());
		}

		return new SiZoneCall($siInput, $guiControlPath);
	}

}