<?php

namespace rocket\cu\gui\control;

use n2n\util\type\attrs\DataSet;

class CuControlCallId implements \JsonSerializable {

	function __construct(private string $controlId) {

	}

	function getControlId(): string {
		return $this->controlId;
	}

	function jsonSerialize(): mixed {
		return [
			'controlId' => $this->controlId
		];
	}

	static function parse(array $data): CuControlCallId {
		$ds = new DataSet($data);

		try {
			return new CuControlCallId($ds->reqString('controlId'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}