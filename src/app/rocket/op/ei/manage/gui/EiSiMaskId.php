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

namespace rocket\op\ei\manage\gui;

use n2n\util\type\attrs\DataMap;
use n2n\util\StringUtils;
use rocket\op\spec\TypePath;
use JsonException;
use n2n\util\type\attrs\AttributesException;
use rocket\op\ei\mask\EiMask;

class EiSiMaskId implements \Stringable {

	function __construct(public readonly TypePath $eiTypePath, public readonly int $viewMode) {

	}

	function __toString(): string {
		return json_encode(['eiTypePath' => (string) $this->eiTypePath, 'viewMode' => $this->viewMode]);
	}

	/**
	 * @param string $str
	 * @return EiSiMaskId
	 * @throws \n2n\util\type\attrs\AttributesException
	 */
	static function fromString(string $str): EiSiMaskId {
		try {
			$dm = new DataMap(StringUtils::jsonDecode($str, true));
			return new EiSiMaskId(TypePath::create($dm->reqString('eiTypePath')), $dm->reqInt('viewMode'));
		} catch (JsonException|\InvalidArgumentException $e) {
			throw new AttributesException('Invalid mask id: ' . $str, previous: $e);
		}
	}

}
