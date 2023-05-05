<?php

namespace rocket\cu\gui;

use rocket\si\content\SiGui;
use n2n\util\uri\Url;
use rocket\si\input\SiInput;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\si\input\SiInputError;
use rocket\cu\gui\control\CuControlCallId;
use n2n\core\container\N2nContext;

interface CuGui {

	function toSiGui(?Url $zoneApiUrl): SiGui;

	/**
	 * @param SiInput $siInput
	 * @return SiInputError|null
	 * @throws CorruptedSiInputDataException
	 */
	function handleSiInput(SiInput $siInput, N2nContext $n2NContext): ?SiInputError;

	/**
	 * @param CuControlCallId $cuControlCallId
	 * @return void
	 * @throws CorruptedSiInputDataException
	 */
	function handleCall(CuControlCallId $cuControlCallId): void;
}