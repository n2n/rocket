<?php

namespace rocket\cu\gui\control;

use rocket\si\control\SiControl;
use n2n\util\uri\Url;
use rocket\ei\manage\api\ApiControlCallId;
use rocket\ei\manage\api\ZoneApiControlCallId;

interface CuControl {

	function getId(): string;

//	function handle(): void;

	function toSiControl(Url $apiUrl, ApiControlCallId|ZoneApiControlCallId $siApiCallId): SiControl;
}