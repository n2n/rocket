<?php

namespace rocket\cu\gui\control;

use n2n\util\uri\Url;
use rocket\si\control\SiCallResponse;

interface CuControl {

	function getId(): string;

	function handle(): void;

	function toSiControl(Url $apiUrl, CuControlCallId $cuControlCallId): SiCallResponse;
}