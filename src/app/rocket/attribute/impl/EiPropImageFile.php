<?php

namespace rocket\attribute\impl;

use Attribute;
use rocket\ui\gui\field\impl\file\ImageDimensionsImportMode;
use n2n\util\type\ArgUtils;
use n2n\io\managed\img\ImageDimension;
use n2n\io\img\impl\ImageSourceFactory;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EiPropImageFile extends EiPropFile {

	function __construct(?int $maxSize = null,
			ImageDimensionsImportMode $dimensionImportMode = ImageDimensionsImportMode::ALL,
			array $extraThumbDimensions = []) {
		parent::__construct($maxSize, ImageSourceFactory::getSupportedExtensions(),
				ImageSourceFactory::getSupportedMimeTypes(), $dimensionImportMode, $extraThumbDimensions);
	}

}