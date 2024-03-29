<?php

namespace rocket\op\cu\gui;

use rocket\si\content\SiGui;
use n2n\util\uri\Url;
use rocket\si\input\SiInput;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\si\input\SiInputError;
use rocket\op\cu\gui\control\CuControlCallId;
use n2n\core\container\N2nContext;
use rocket\si\control\SiCallResponse;
use rocket\op\cu\util\Cuu;
use rocket\si\content\SiValueBoundary;

interface CuGui {

	function toSiGui(?Url $zoneApiUrl): SiGui;

	/**
	 * @param SiInput $siInput
	 * @param N2nContext $n2nContext
	 * @return SiInputError|null
	 * @throws CorruptedSiInputDataException
	 */
	function handleSiInput(SiInput $siInput, N2nContext $n2nContext): ?SiInputError;

	/**
	 * @return SiValueBoundary[]
	 */
	function getInputSiValueBoundaries(): array;

	/**
	 * @param CuControlCallId $cuControlCallId
	 * @param Cuu $cuu
	 * @return SiCallResponse
	 * @throws CorruptedSiInputDataException
	 */
	function handleCall(CuControlCallId $cuControlCallId, Cuu $cuu): SiCallResponse;
}