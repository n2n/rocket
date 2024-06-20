<?php

namespace rocket\ui\si\api;

class SiControlCall {

	/**
	 * @param string $maskId
	 * @param string|null $entryId if not null the call is meant for an entry control
	 * @param string $controlName
	 */
	function __construct(private string $maskId, private ?string $entryId, private string $controlName) {

	}
}