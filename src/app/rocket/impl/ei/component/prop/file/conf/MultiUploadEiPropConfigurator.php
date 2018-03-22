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
namespace rocket\impl\ei\component\prop\file\conf;

use rocket\impl\ei\component\prop\adapter\AdaptableEiPropConfigurator;
use rocket\ei\component\EiSetup;
use rocket\impl\ei\component\prop\file\FileEiProp;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\StringArrayMag;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\impl\ei\component\prop\file\command\ThumbEiCommand;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\persistence\meta\structure\Column;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\io\managed\img\ImageDimension;
use n2n\util\config\LenientAttributeReader;
use rocket\impl\ei\component\prop\file\command\MultiUploadEiCommand;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use rocket\ei\manage\generic\UnknownScalarEiPropertyException;

class MultiUploadFileEiPropConfigurator extends FileEiPropConfigurator {
	const ATTR_CHECK_IMAGE_MEMORY_KEY = 'checkImageResourceMemory';
	
	const ATTR_ALLOWED_EXTENSIONS_KEY = 'allowedExtensions';
	
	const ATTR_DIMENSION_IMPORT_MODE_KEY = 'dimensionImportMode';
	
	const ATTR_EXTRA_THUMB_DIMENSIONS_KEY = 'extraThumbDimensions';
	
	const ATTR_MULTI_UPLOAD_AVAILABLE_KEY = 'multiUploadAvailable';
	
	const ATTR_MULTI_UPLOAD_NAMING_FIELD_PATH_KEY = 'multiUploadNamingFieldId'; 
	
	private $fileEiProp;
	
	public function __construct(FileEiProp $fileEiProp) {
		parent::__construct($fileEiProp);
	
		$this->fileEiProp = $fileEiProp;
		$this->autoRegister();
	}
	
	public function setup(EiSetup $setupProcess) {
		parent::setup($setupProcess);
	
		$this->fileEiProp->setAllowedExtensions($this->attributes->getScalarArray(self::ATTR_ALLOWED_EXTENSIONS_KEY,
				false, $this->fileEiProp->getAllowedExtensions(), true));
		
		$this->fileEiProp->setCheckImageMemoryEnabled($this->attributes->getBool(self::ATTR_CHECK_IMAGE_MEMORY_KEY, 
				false, $this->fileEiProp->isCheckImageMemoryEnabled()));
		
		$this->fileEiProp->setImageDimensionImportMode($this->attributes->getEnum(self::ATTR_DIMENSION_IMPORT_MODE_KEY,
				FileEiProp::getImageDimensionImportModes(), false, $this->fileEiProp->getImageDimensionImportMode(), true));
		
		$extraImageDimensions = array();
		if ($this->attributes->contains(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY)) {
			foreach ($this->attributes->getScalarArray(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY) as $imageDimensionStr) {
				try {
					$extraImageDimensions[$imageDimensionStr] = ImageDimension::createFromString($imageDimensionStr);
				} catch (\InvalidArgumentException $e) {
					throw $setupProcess->createException('Invalid ImageDimension string: ' . $imageDimensionStr, $e);
				}
			}
			$this->fileEiProp->setExtraImageDimensions($extraImageDimensions);
		}
		
		$thumbEiCommand = new ThumbEiCommand($this->fileEiProp);
		$setupProcess->eiu()->mask()->supremeMask()->addEiCommand($thumbEiCommand);
		$this->fileEiProp->setThumbEiCommand($thumbEiCommand);
		
		if ($this->attributes->getBool(self::ATTR_MULTI_UPLOAD_AVAILABLE_KEY, false)) {
			$namingEiPropPath = null;
			if ($this->attributes->contains(self::ATTR_MULTI_UPLOAD_NAMING_FIELD_PATH_KEY)) {
				try {
					$namingEiPropPath = $setupProcess->getScalarEiPropertyByFieldPath(
							$this->attributes->getString(self::ATTR_MULTI_UPLOAD_NAMING_FIELD_PATH_KEY))->getEiPropPath();
				} catch (\InvalidArgumentException $e) {
					throw $setupProcess->createException('Invalid base ScalarEiProperty configured.', $e);
				} catch (UnknownScalarEiPropertyException $e) {
					throw $setupProcess->createException('Configured base ScalarEiProperty not found.', $e);
				}
			}
			
			$multiUploadEiCommand = new MultiUploadEiCommand($this->fileEiProp, $namingEiPropPath);
			$this->fileEiProp->getEiMask()->getSupremeEiMask()->getEiCommandCollection()
					->add($multiUploadEiCommand);
			$this->fileEiProp->setMultiUploadEiCommand($multiUploadEiCommand);
		}
	}
	
	public function initAutoEiPropAttributes(Column $column = null) {
		parent::initAutoEiPropAttributes($column);
		
		if (false !== stripos($this->requireEntityProperty()->getName(), 'image')) {
			$this->attributes->set(self::ATTR_ALLOWED_EXTENSIONS_KEY, array('png', 'jpg', 'jpeg', 'gif'));
			$this->attributes->set(self::ATTR_DIMENSION_IMPORT_MODE_KEY, FileEiProp::DIM_IMPORT_MODE_ALL);
		}
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
	
		$lar = new LenientAttributeReader($this->attributes);
				
		$magCollection->addMag(self::ATTR_ALLOWED_EXTENSIONS_KEY, new StringArrayMag('Allowed Extensions', 
				$lar->getScalarArray(self::ATTR_ALLOWED_EXTENSIONS_KEY, $this->fileEiProp->getAllowedExtensions()), 
				false));
		
		$magCollection->addMag(self::ATTR_DIMENSION_IMPORT_MODE_KEY, new EnumMag('Dimensions import mode', 
				array(FileEiProp::DIM_IMPORT_MODE_ALL => 'All possible dimensions',
						FileEiProp::DIM_IMPORT_MODE_USED_ONLY => 'Only for current image used dimensions'),
				$lar->getString(self::ATTR_DIMENSION_IMPORT_MODE_KEY, 
						$this->fileEiProp->getImageDimensionImportMode())));
		
		$extraImageDimensionStrs = array();
		if ($lar->contains(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY)) {
			$extraImageDimensionStrs = $lar->getScalarArray(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY);
		} else {
			foreach ((array) $this->fileEiProp->getExtraImageDimensions() as $extraImageDimension) {
				$extraImageDimensionStrs[] = (string) $extraImageDimension;
			}
		}
		$magCollection->addMag(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY, new StringArrayMag('Extra Thumb Dimensions', 
				$extraImageDimensionStrs, false));
		
		$magCollection->addMag(self::ATTR_CHECK_IMAGE_MEMORY_KEY, new BoolMag('Check Image Resource Memory',
				$lar->getBool(self::ATTR_CHECK_IMAGE_MEMORY_KEY, $this->fileEiProp->isCheckImageMemoryEnabled())));
		
		$enablerMag = new TogglerMag('Multi upload',
				$lar->getBool(self::ATTR_MULTI_UPLOAD_AVAILABLE_KEY, false));
		$magCollection->addMag(self::ATTR_MULTI_UPLOAD_AVAILABLE_KEY, $enablerMag);
		
		$enumMag = new EnumMag('Naming Field', 
				$this->getNamingEiPropIdOptions(), $lar->getString(self::ATTR_MULTI_UPLOAD_NAMING_FIELD_PATH_KEY));
		$enablerMag->setOnAssociatedMagWrappers(array($magCollection->addMag(self::ATTR_MULTI_UPLOAD_NAMING_FIELD_PATH_KEY, $enumMag)));
		
		return $magDispatchable;
	}
	
	private function getNamingEiPropIdOptions() {
		$namingEiPropIdOptions = array();
		foreach ($this->eiComponent->getEiMask()->getEiEngine()->getScalarEiDefinition()->getMap()
				as $id => $genericScalarProperty) {
			if ($id === $this->eiComponent->getId()) continue;
			$namingEiPropIdOptions[$id] = (string) $genericScalarProperty->getLabelLstr();
		}
		return $namingEiPropIdOptions;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$this->attributes->appendAll($magDispatchable->getMagCollection()->readValues(array(
				self::ATTR_ALLOWED_EXTENSIONS_KEY, self::ATTR_DIMENSION_IMPORT_MODE_KEY, 
				self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY, self::ATTR_CHECK_IMAGE_MEMORY_KEY,
				self::ATTR_MULTI_UPLOAD_AVAILABLE_KEY, self::ATTR_MULTI_UPLOAD_NAMING_FIELD_PATH_KEY), true), true);
	}
}
