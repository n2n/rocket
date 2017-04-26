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
namespace rocket\spec\ei\component\field\impl\file\command\model;

use n2n\io\fs\img\ImageDimension;
use n2n\impl\web\dispatch\map\val\ValEnum;
use n2n\impl\web\dispatch\map\val\ValNumeric;
use n2n\io\managed\img\ImageFile;
use n2n\web\dispatch\Dispatchable;
use n2n\io\img\ImageResource;
use n2n\util\StringUtils;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\io\managed\File;

class ThumbModel implements Dispatchable{
	private $imageFile;
	private $imageDimensions;
	
	public $imageDimensionStr;
	public $x;
	public $y;
	public $width;
	public $height;
	public $keepAspectRatio = true;
	
	public function __construct(ImageFile $imageFile, array $imageDimensions) {
		$this->imageFile = $imageFile;
		$this->imageDimensions = $imageDimensions;
	}
	
	public function getImageFile(): ImageFile {
		return $this->imageFile;
	}
	
	public function getImageDimensions(): array {
		return $this->imageDimensions;
	}
	
	public function getImageDimensionOptions(): array {
		$options = array();
		foreach ($this->imageDimensions as $imageDimension) {
			$idExt = $imageDimension->getIdExt();
			$options[$imageDimension->__toString()] = $imageDimension->getWidth() . ' x ' . $imageDimension->getHeight() 
					. ($idExt !== null ? ' (' . StringUtils::pretty($idExt) . ')' : '');
		}
		return $options;
	}
	
	private function _validation(BindingDefinition $bd) {
		$bd->val(array('x', 'y', 'width', 'height'), new ValNumeric(true, null, 0));
		$bd->val('imageDimensionStr', new ValEnum(array_keys($this->imageDimensions)));
	}
	
	public function save() {
		$imageDimension = $this->imageDimensions[$this->imageDimensionStr];
		
		$imageResource = $this->imageFile->getImageSource()->createImageResource();
		$imageResource->crop($this->x, $this->y, $this->width, $this->height);
		$imageResource->proportionalResize($imageDimension->getWidth(), $imageDimension->getHeight(), 
				ImageResource::AUTO_CROP_MODE_CENTER);
		$thumbImageFile = new ImageFile($this->imageFile->createThumbFile($imageDimension, $imageResource));
		$imageResource->destroy();
		
		foreach ($thumbImageFile->getVariationImageDimension() as $imageDimension) {
			$imageResource = $thumbImageFile->getImageSource()->createImageResource();
			$imageResource->proportionalResize($imageDimension->getWidth(), $imageDimension->getHeight(), 
					ImageResource::AUTO_CROP_MODE_CENTER);
			$thumbImageFile->createVariationFile($imageDimension, $imageResource);
			$imageResource->destroy();
		}
	}
}

// if (isset($_POST['formatbreite']) && isset($_POST['formathoehe']) && isset($_POST['xwert'])
// 		&& isset($_POST['ywert']) && isset($_POST['ausschnittbreite']) && isset($_POST['ausschnitthoehe'])
// 		&& isset($_POST['anschneiden'])
// ) {
// 	$dimension = $this->getDimension($imageEiProp, $_POST['formatbreite'], $_POST['formathoehe']);
// 	if (!$dimension) {
// 		$mc->addError($text->get('err_image_resize_invalid_format',
// 				array('width' => $_POST['formatbreite'], 'height' => $_POST['formathoehe'])));
// 	} else {
// 		// @todo: @_POST['anschneiden'] has to be inverted, in order to give correct result!
// 		$resizeModel->updateThumb($dimension, $_POST['xwert'], $_POST['ywert'], $_POST['ausschnittbreite'],
// 				$_POST['ausschnitthoehe'], !(boolean) $_POST['anschneiden']);
	
// 		$mc->addInfo($text->get('image_resize_thumb_created',
// 				array('width' => $dimension->getWidth(), 'height' => $dimension->getHeight())));

// 	}
// }

// public function updateThumb(NN6FileImageDimension $dimension, $x, $y, $width, $height, $crop) {
// 	$fileManager = $this->image->getFileManager();

// 	$endWidth = $dimension->getWidth();
// 	$endHeight = $dimension->getHeight();
// 	$crop = ($dimension->getCrop() || $crop);
// 	$imageResource = $this->image->createResource();
// 	$imageResource->crop($x, $y, $width, $height);
// 	$imageResource->resize($endWidth, $endHeight, $crop);

// 	$fileManager->setImageResFromResource($this->image, $dimension, $imageResource);
// 	$imageResource->destroy();
// }
