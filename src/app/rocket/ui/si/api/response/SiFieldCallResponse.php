<?php

namespace rocket\ui\si\api\response;

class SiFieldCallResponse {


	function __construct(private array $data) {

	}

	/**
	 * @return array
	 */
	public function getData(): array {
		return $this->data;
	}

	function jsonSerialize(): mixed {
		return ['data' => $this->data];
	}
}