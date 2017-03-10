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
namespace rocket\spec\ei\component\field\impl\file\conf;

use rocket\spec\ei\component\field\impl\adapter\AdaptableEiFieldConfigurator;
use rocket\spec\ei\component\EiSetupProcess;
use rocket\spec\ei\component\field\impl\file\FileEiField;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\StringArrayMag;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\spec\ei\component\field\impl\file\command\ThumbEiCommand;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\persistence\meta\structure\Column;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\io\managed\img\ImageDimension;
use n2n\util\config\LenientAttributeReader;
use rocket\spec\ei\component\field\impl\file\command\MultiUploadEiCommand;

class FileEiFieldConfigurator extends AdaptableEiFieldConfigurator {
	const ATTR_CHECK_IMAGE_MEMORY_KEY = 'checkImageResourceMemory';
	
	const ATTR_ALLOWED_EXTENSIONS_KEY = 'allowedExtensions';
	
	const ATTR_DIMENSION_IMPORT_MODE_KEY = 'dimensionImportMode';
	
	const ATTR_EXTRA_THUMB_DIMENSIONS_KEY = 'extraThumbDimensions';
	
	const ATTR_MULTI_UPLOAD_AVAILABLE_KEY = 'multiUploadAvailable';
	
	private $fileEiField;
	
	public function __construct(FileEiField $fileEiField) {
		parent::__construct($fileEiField);
	
		$this->fileEiField = $fileEiField;
		$this->autoRegister();
	}
	
	public function setup(EiSetupProcess $setupProcess) {
		parent::setup($setupProcess);
	
		$this->fileEiField->setAllowedExtensions($this->attributes->getScalarArray(self::ATTR_ALLOWED_EXTENSIONS_KEY,
				false, $this->fileEiField->getAllowedExtensions(), true));
		
		$this->fileEiField->setCheckImageMemoryEnabled($this->attributes->getBool(self::ATTR_CHECK_IMAGE_MEMORY_KEY, 
				false, $this->fileEiField->isCheckImageMemoryEnabled()));
		
		$this->fileEiField->setImageDimensionImportMode($this->attributes->getEnum(self::ATTR_DIMENSION_IMPORT_MODE_KEY,
				FileEiField::getImageDimensionImportModes(), false, $this->fileEiField->getImageDimensionImportMode(), true));
		
		$extraImageDimensions = array();
		if ($this->attributes->contains(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY)) {
			foreach ($this->attributes->getScalarArray(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY) as $imageDimensionStr) {
				try {
					$extraImageDimensions[$imageDimensionStr] = ImageDimension::createFromString($imageDimensionStr);
				} catch (\InvalidArgumentException $e) {
					throw $setupProcess->createException('Invalid ImageDimension string: ' . $imageDimensionStr, $e);
				}
			}
			$this->fileEiField->setExtraImageDimensions($extraImageDimensions);
		}
		
		$thumbEiCommand = new ThumbEiCommand($this->fileEiField);
		$this->fileEiField->getEiEngine()->getSupremeEiThing()->getEiEngine()->getEiCommandCollection()->add($thumbEiCommand);
		$this->fileEiField->setThumbEiCommand($thumbEiCommand);
		
		if ($this->attributes->getBool(self::ATTR_MULTI_UPLOAD_AVAILABLE_KEY, false)) {
			$this->fileEiField->getEiEngine()->getSupremeEiThing()->getEiEngine()->getEiCommandCollection()
					->add(new MultiUploadEiCommand($this->fileEiField));
		}
	}
	
	public function initAutoEiFieldAttributes(Column $column = null) {
		parent::initAutoEiFieldAttributes($column);
		
		if (false !== stripos($this->requireEntityProperty()->getName(), 'image')) {
			$this->attributes->set(self::ATTR_ALLOWED_EXTENSIONS_KEY, array('png', 'jpg', 'jpeg', 'gif'));
			$this->attributes->set(self::ATTR_DIMENSION_IMPORT_MODE_KEY, FileEiField::DIM_IMPORT_MODE_ALL);
		}
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
	
		$lar = new LenientAttributeReader($this->attributes);
				
		$magCollection->addMag(new StringArrayMag(self::ATTR_ALLOWED_EXTENSIONS_KEY, 'Allowed Extensions', 
				$lar->getScalarArray(self::ATTR_ALLOWED_EXTENSIONS_KEY, $this->fileEiField->getAllowedExtensions()), 
				false));
		
		$magCollection->addMag(new EnumMag(self::ATTR_DIMENSION_IMPORT_MODE_KEY, 'Dimensions import mode', 
				array(FileEiField::DIM_IMPORT_MODE_ALL => 'All possible dimensions',
						FileEiField::DIM_IMPORT_MODE_USED_ONLY => 'Only for current image used dimensions'),
				$lar->getString(self::ATTR_DIMENSION_IMPORT_MODE_KEY, 
						$this->fileEiField->getImageDimensionImportMode())));
		
		$extraImageDimensionStrs = array();
		if ($lar->contains(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY)) {
			$extraImageDimensionStrs = $lar->getScalarArray(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY);
		} else {
			foreach ((array) $this->fileEiField->getExtraImageDimensions() as $extraImageDimension) {
				$extraImageDimensionStrs[] = (string) $extraImageDimension;
			}
		}
		$magCollection->addMag(new StringArrayMag(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY, 'Extra Thumb Dimensions', 
				$extraImageDimensionStrs, false));
		
		$magCollection->addMag(new BoolMag(self::ATTR_CHECK_IMAGE_MEMORY_KEY, 'Check Image Resource Memory',
				$lar->getBool(self::ATTR_CHECK_IMAGE_MEMORY_KEY, $this->fileEiField->isCheckImageMemoryEnabled())));
		
		$magCollection->addMag(new BoolMag(self::ATTR_MULTI_UPLOAD_AVAILABLE_KEY, 'Multi upload',
				$lar->getBool(self::ATTR_MULTI_UPLOAD_AVAILABLE_KEY, false)));
		
		return $magDispatchable;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$this->attributes->appendAll($magDispatchable->getMagCollection()->readValues(array(
				self::ATTR_ALLOWED_EXTENSIONS_KEY, self::ATTR_DIMENSION_IMPORT_MODE_KEY, 
				self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY, self::ATTR_CHECK_IMAGE_MEMORY_KEY,
				self::ATTR_MULTI_UPLOAD_AVAILABLE_KEY), true), true);
	}
}
