<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ui\si\content;

use n2n\util\ex\IllegalStateException;
use n2n\web\http\UploadDefinition;
use n2n\util\type\attrs\AttributesException;
use n2n\core\container\N2nContext;
use InvalidArgumentException;
use rocket\ui\si\err\CorruptedSiDataException;

interface SiField {
	
	/**
	 * @return string
	 */
	function getType(): string;
	
	/**
	 * @return array
	 */
	function toJsonStruct(N2nContext $n2nContext): array;
	
	/**
	 * @return bool
	 */
	function isReadOnly(): bool;

	/**
	 * @param array $data
	 * @param N2nContext $n2nContext
	 * @throws IllegalStateException if readonly ({@link self::isReadyOnly()} returns true).
	 * @throws CorruptedSiDataException if data is corrupt
	 */
	function handleInput(array $data, N2nContext $n2nContext): bool;

//	function flush(N2nContext $n2nContext): void;
	
	/**
	 * @return bool
	 */
	function isCallable(): bool;

	/**
	 * @param array $data
	 * @param UploadDefinition[] $uploadDefinitions
	 * @param N2nContext $n2nContext
	 * @return array
	 * @throws CorruptedSiDataException
	 */
	function handleCall(array $data, array $uploadDefinitions, N2nContext $n2nContext): array;
}
