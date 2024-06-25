<?php

namespace rocket\op\cu\gui\control;

use n2n\util\uri\Url;
use SiCallResponse;
use rocket\op\cu\util\Cuu;
use rocket\ui\si\control\SiControl;

interface CuControl {

	function getId(): string;

	function handle(Cuu $cuu): SiCallResponse;

	function toSiControl(Url $apiUrl, CuControlCallId $cuControlCallId): SiControl;
}