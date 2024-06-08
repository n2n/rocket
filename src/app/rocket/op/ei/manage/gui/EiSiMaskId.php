<?php

namespace rocket\op\ei\manage\gui;

use n2n\util\type\attrs\DataMap;
use n2n\util\StringUtils;
use rocket\op\spec\TypePath;
use JsonException;
use n2n\util\type\attrs\AttributesException;

class EiSiMaskId {

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
			$dm = new DataMap(StringUtils::jsonDecode($str));
			return new EiSiMaskId(TypePath::create($dm->reqString('eiTypePath')), $dm->reqInt('viewMode'));
		} catch (JsonException|\InvalidArgumentException $e) {
			throw new AttributesException('Invalid mask id: ' . $str, previous: $e);
		}

	}
}
