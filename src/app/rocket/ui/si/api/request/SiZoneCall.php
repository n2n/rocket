<?php

namespace rocket\ui\si\api\request;

use n2n\web\http\controller\impl\ControllingUtils;
use n2n\web\http\Method;
use rocket\ui\si\input\SiInputFactory;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\web\http\StatusException;
use n2n\web\http\BadRequestException;

class SiZoneCall implements \JsonSerializable {

	function __construct(private ?SiInput $input, private string $zoneControlName) {

	}

	function getInput(): ?SiInput {
		return $this->input;
	}

	function getZoneControlName(): string {
		return $this->zoneControlName;
	}


	/**
	 * @throws StatusException
	 */
	static function fromCu(ControllingUtils $cu): ?SiZoneCall {
		if (!($cu->getRequest()->getMethod() === Method::POST)) {
			return null;
		}

		$param = $cu->getParamPost('si-zone-call');
		if ($param === null) {
			return null;
		}

		$httpData = $param->parseJsonToHttpData();
		$zoneControlName = $httpData->reqString('zoneControlName');


		$siInput = null;
		if (null !== ($inputData = $httpData->optArray('input'))) {
			try {
				$siInput = SiInput::parse($inputData);
			} catch (CorruptedSiDataException $e) {
				throw new BadRequestException(previous: $e);
			}
		}

		return new SiZoneCall($siInput, $zoneControlName);
	}

	public function jsonSerialize(): mixed {
		return [
			'zoneControlName' => $this->zoneControlName,
			'input' => $this->input
		];
	}
}