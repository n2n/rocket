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

use n2n\io\managed\File;
use rocket\impl\ei\component\prop\file\command\ThumbEiCommand;
use n2n\io\managed\img\ImageDimension;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\Eiu;
use rocket\si\content\impl\SiFile;
use n2n\io\managed\img\ImageFile;
use n2n\util\type\ArgUtils;
use n2n\io\managed\impl\TmpFileManager;
use n2n\util\type\CastUtils;
use rocket\si\content\impl\SiImageDimension;
use n2n\util\StringUtils;

class ThumbResolver {
	
	const DIM_IMPORT_MODE_ALL = 'all';
	const DIM_IMPORT_MODE_USED_ONLY = 'usedOnly';
	
	private $thumbEiCommand;
	private $imageDimensionsImportMode = null;
	private $extraImageDimensions = array();
	
	public function setThumbEiCommand(ThumbEiCommand $thumbEiCommand) {
// 		$thumbEiCommand->setFileEiProp($this);
		$this->thumbEiCommand = $thumbEiCommand;
	}
	
	public function getThumbEiCommand() {
		return $this->thumbEiCommand;
	}
	
	public function getImageDimensionImportMode() {
		return $this->imageDimensionsImportMode;
	}
	
	public function setImageDimensionImportMode(?string $imageDimensionImportMode) {
		ArgUtils::valEnum($imageDimensionImportMode, self::getImageDimensionImportModes(), null, true);
		$this->imageDimensionsImportMode = $imageDimensionImportMode;
	}
	
	public static function getImageDimensionImportModes(): array {
		return array(self::DIM_IMPORT_MODE_ALL, self::DIM_IMPORT_MODE_USED_ONLY);
	}
	
	public function getExtraImageDimensions() {
		return $this->extraImageDimensions;
	}
	
	public function setExtraImageDimensions(array $extraImageDimensions) {
		$this->extraImageDimensions = $extraImageDimensions;
	}
	
	/**
	 * @param File $file
	 * @param Eiu $eiu
	 * @return \rocket\si\content\impl\SiFile
	 */
	function createSiFile(File $file, Eiu $eiu, bool $imageSupported) {
		if (!$file->isValid()) {
			return new SiFile(FileId::create($file), $eiu->dtc('rocket')->t('missing_file_err'));
		}
		
		$fileSource = $file->getFileSource();
		
		$siFile = new SiFile(FileId::create($file), $file->getOriginalName());
		
		$tmpQualifiedName = null;
		if ($eiu->lookup(TmpFileManager::class)
				->containsSessionFile($file, $eiu->getN2nContext()->getHttpContext()->getSession())) {
			$tmpQualifiedName = $fileSource->getQualifiedName();
		}
		
		if ($fileSource->isHttpAccessible()) {
			$siFile->setUrl($fileSource->getUrl());
		} else if ($tmpQualifiedName !== null) {
			$siFile->setUrl($this->createTmpUrl($eiu, $tmpQualifiedName));
		} else {
			$siFile->setUrl($this->createFileUrl($eiu, $eiu->entry()->getPid()));
		}
		
		if (!$imageSupported) {
			return $siFile;
		}
		
		$thumbFile = $this->buildThumbFile($file);
		
		if ($thumbFile === null) {
			return $siFile;
		}
		
		if ($thumbFile->getFileSource()->isHttpAccessible()) {
			$siFile->setThumbUrl($thumbFile->getFileSource()->getUrl());
		} else if ($tmpQualifiedName !== null) {
			$siFile->setThumbUrl($this->createTmpThumbUrl($eiu, $tmpQualifiedName, 
					SiFile::getThumbStrategy()->getImageDimension()));
		} else {
			$siFile->setThumbUrl($this->createThumbUrl($eiu,
					SiFile::getThumbStrategy()->getImageDimension()));
		}
		
		$siFile->setImageDimensions($this->createSiImageDimensions($this->determineImageDimensions($file)));
		
		return $siFile;
	}
	
	function determineFile(FileId $fileId, Eiu $eiu): ?File {
		if ($fileId->getFileManagerName() === TmpFileManager::class) {
			$tfm = $eiu->lookup(TmpFileManager::class);
			CastUtils::assertTrue($tfm instanceof TmpFileManager);
			
			return $tfm->getSessionFile($fileId->getQualifiedName(), 
					$eiu->getN2nContext()->getHttpContext()->getSession());
		}
		
		$file = $eiu->field()->getValue();
		CastUtils::assertTrue($file instanceof File);
		if ($file !== null && $fileId->matches($file)) {
			return $file;
		}
		
		return null;
	}
		
	/**
	 * @param File $file
	 * @return \n2n\io\managed\File|null
	 */
	private function buildThumbFile(File $file) {
		if (!$file->getFileSource()->getVariationEngine()->hasThumbSupport()) {
			return null;
		}
		
		$thumbStartegy = SiFile::getThumbStrategy();
		return (new ImageFile($file))->getOrCreateThumb($thumbStartegy)->getFile();
	}
	
	function createFileUrl(Eiu $eiu, string $pid) {
		return $eiu->frame()->getCmdUrl($this->thumbEiCommand)->extR(['file', $pid]);
	}
		
	/**
	 * @param EiuFrame $eiuFrame
	 * @param ImageDimension $imageDimension
	 * @return \n2n\util\uri\Url
	 */
	function createThumbUrl(Eiu $eiu, ImageDimension $imageDimension) {
		return $eiu->frame()->getCmdUrl($this->thumbEiCommand)
				->extR(['thumb', $eiu->entry()->getPid()], ['imgDim' => $imageDimension->__toString()]);
	}
	
	/**
	 * @param Eiu $eiu
	 * @param string $qualifiedName
	 * @return \n2n\util\uri\Url
	 */
	function createTmpUrl(Eiu $eiu, string $qualifiedName) {
		return $eiu->frame()->getCmdUrl($this->thumbEiCommand)->extR(['tmp'], ['qn' => $qualifiedName]);
	}
	
	/**
	 * @param Eiu $eiu
	 * @param string $qualifiedName
	 * @param ImageDimension $imageDimension
	 * @return \n2n\util\uri\Url
	 */
	function createTmpThumbUrl(Eiu $eiu, string $qualifiedName, ImageDimension $imageDimension) {
		return $eiu->frame()->getCmdUrl($this->thumbEiCommand)->extR(['tmpthumb'], 
				['qn' => $qualifiedName, 'imgDim' => $imageDimension->__toString()]);
	}
	
	/**
	 * @param File $file
	 * @return boolean
	 */
	function isThumbCreationEnabled(File $file) {
		if ($this->thumbEiCommand === null
				|| !$file->getFileSource()->getVariationEngine()->hasThumbSupport()) return false;
				
		if (!empty($this->extraImageDimensions)) return true;
		
		$thumbEngine = $file->getFileSource()->getThumbManager();
		switch ($this->imageDimensionsImportMode) {
			case self::DIM_IMPORT_MODE_ALL:
				return !empty($thumbEngine->getPossibleImageDimensions());
			case self::DIM_IMPORT_MODE_USED_ONLY:
				return !empty($thumbEngine->getUsedImageDimensions());
			default:
				return false;
		}
	}
	
	/**
	 * @param ImageDimension[] $imageDimensions
	 * @return \rocket\si\content\impl\SiImageDimension[]
	 */
	private function createSiImageDimensions($imageDimensions) {
		$siImageDimensions = []; 
		foreach ($imageDimensions as $id => $imageDimension) {
			$siImageDimensions[] = new SiImageDimension($id, StringUtils::pretty($id), 
					$imageDimension->getWidth(), $imageDimension->getHeight());
		}
		return $siImageDimensions;
	}
	
	/**
	 * @param File $file
	 * @return ImageDimension[]
	 */
	function determineImageDimensions(File $file) {
		$imageDimensions = array();
		
		if (!$file->getFileSource()->getVariationEngine()->hasThumbSupport()) {
			return $imageDimensions;
		}
		
		foreach ($this->extraImageDimensions as $imageDimension) {
			$imageDimensions[(string) $imageDimension] = $imageDimension;
		}
		
		$thumbEngine = $file->getFileSource()->getVariationEngine()->getThumbManager();
		
		$autoImageDimensions = array();
		switch ($this->imageDimensionsImportMode) {
			case self::DIM_IMPORT_MODE_ALL:
				$autoImageDimensions = $thumbEngine->getPossibleImageDimensions();
				break;
			case self::DIM_IMPORT_MODE_USED_ONLY:
				$autoImageDimensions = $thumbEngine->getUsedImageDimensions();
				break;
		}
		
		$rocketImageDimensionStr = (string) SiFile::getThumbStrategy()->getImageDimension();
		
		foreach ($autoImageDimensions as $autoImageDimension) {
			$autoImageDimensionStr = (string) $autoImageDimension;
			
			if ($autoImageDimensionStr == $rocketImageDimensionStr) {
				continue;
			}
			
			$imageDimensions[$autoImageDimensionStr] = $autoImageDimension;
		}
		
		return $imageDimensions;
	}
}