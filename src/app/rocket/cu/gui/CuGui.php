<?php

namespace rocket\cu\gui;

use rocket\si\content\SiGui;
use n2n\util\uri\Url;
use rocket\si\input\SiInput;
use rocket\si\input\CorruptedSiInputDataException;

interface CuGui {

	function toSiGui(?Url $zoneApiUrl): SiGui;

	/**
	 * @param SiInput $siInput
	 * @return void
	 * @throws CorruptedSiInputDataException
	 */
	function handleSiInput(SiInput $siInput): void;
}