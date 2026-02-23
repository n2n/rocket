<?php

namespace rocket\attribute\impl;

use Attribute;
use rocket\ui\gui\field\impl\file\ImageDimensionsImportMode;
use n2n\util\type\ArgUtils;
use n2n\io\managed\img\ImageDimension;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EiPropFile {

	function __construct(public ?int $maxSize = null, public readonly  ?array $allowedExtensions = null,
			public readonly ?array $allowedMimeTypes = null,
			public ImageDimensionsImportMode $dimensionImportMode = ImageDimensionsImportMode::ALL,
			public readonly array $extraThumbDimensions = [], public bool $imageRecognized = true) {
		ArgUtils::valArray($allowedMimeTypes, 'string', true);
		ArgUtils::valArray($extraThumbDimensions, ImageDimension::class);
	}

}