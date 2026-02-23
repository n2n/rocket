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
namespace rocket\ui\gui\field\impl\file;

use n2n\io\managed\File;
use n2n\io\managed\img\ImageDimension;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\op\ei\util\Eiu;
use rocket\ui\si\content\impl\SiFile;
use n2n\io\managed\img\ImageFile;
use n2n\util\type\ArgUtils;
use n2n\io\managed\impl\TmpFileManager;
use n2n\util\type\CastUtils;
use n2n\util\StringUtils;
use n2n\io\managed\FileManager;
use n2n\io\managed\FileLocator;
use n2n\io\managed\img\ThumbCut;
use rocket\op\ei\EiCmdPath;
use n2n\core\container\N2nContext;
use rocket\ui\si\content\impl\SiFileFactory;
use n2n\l10n\DynamicTextCollection;
use rocket\ui\gui\res\GuiResourceRegistry;
use rocket\ui\gui\res\GuiResourceController;
use rocket\ui\si\content\impl\SiImageDimension;

class GuiSiFileFactory implements SiFileFactory {
	
//	const DIM_IMPORT_MODE_ALL = 'all';
//	const DIM_IMPORT_MODE_USED_ONLY = 'usedOnly';
	
//	private $thumbEiCmdPath;
//
//	private bool $imageAllowed = true;

	function __construct(public bool $imageRecognized) {
	}

//	public function setThumbEiCmdPath(EiCmdPath $thumbEiCmdPath) {
//// 		$thumbEiCommand->setFileEiProp($this);
//		$this->thumbEiCmdPath = $thumbEiCmdPath;
//	}
//
//	function setTargetFileManager(?FileManager $fileManager): void {
//		$this->targetFileManager = $fileManager;
//	}
//
//	function getTargetFileManager(): ?FileLocator {
//		return $this->targetFileManager;
//	}
//
//	function setTargetFileLocator(?FileLocator $fileLocator): void {
//		$this->targetFileLocator = $fileLocator;
//	}
//
//	function getTargetFileLocator() {
//		return $this->targetFileLocator;
//	}
//
//	public function getThumbEiCmdPath() {
//		return $this->thumbEiCmdPath;
//	}
//
//	public function getImageDimensionImportMode() {
//		return $this->imageDimensionsImportMode;
//	}
//
//	public function setImageDimensionImportMode(?string $imageDimensionImportMode) {
//		ArgUtils::valEnum($imageDimensionImportMode, self::getImageDimensionImportModes(), null, true);
//		$this->imageDimensionsImportMode = $imageDimensionImportMode;
//	}
//
//	public static function getImageDimensionImportModes(): array {
//		return array(self::DIM_IMPORT_MODE_ALL, self::DIM_IMPORT_MODE_USED_ONLY);
//	}
//
//	/**
//	 * @return ImageDimension[]
//	 */
//	public function getExtraImageDimensions(): array {
//		return $this->extraImageDimensions;
//	}
//
//	/**
//	 * @param array $extraImageDimensions
//	 * @return void
//	 */
//	public function setExtraImageDimensions(array $extraImageDimensions) {
//		ArgUtils::valArray($extraImageDimensions, ImageDimension::class);
//		$this->extraImageDimensions = $extraImageDimensions;
//	}

	function createSiFile(File $file, N2nContext $n2nContext): SiFile {
		if (!$file->isValid()) {
			$dtc = new DynamicTextCollection('rocket', $n2nContext->getN2nLocale());
			return new SiFile(SiFileId::create($file), $dtc->t('missing_file_err'));
		}

		$fileSource = $file->getFileSource();

		$siFile = new SiFile(SiFileId::create($file), $file->getOriginalName());

		$tmpQualifiedName = $this->determineTmpQualifiedNameIfExists($file, $n2nContext);

		if ($fileSource->isHttpAccessible()) {
			$siFile->setUrl($fileSource->getUrl());
		} else if ($tmpQualifiedName !== null) {
			$fileAccessToken = $n2nContext->lookup(GuiResourceRegistry::class)
					->registerFile(TmpFileManager::class, $tmpQualifiedName);
			$siFile->setUrl(GuiResourceController::determineFileUrl($fileAccessToken, $n2nContext));
		} else {
			$fileAccessToken = $n2nContext->lookup(GuiResourceRegistry::class)->registerFile(
					$file->getFileSource()->getFileManagerName(),
					$file->getFileSource()->getQualifiedName());

			$siFile->setUrl(GuiResourceController::determineFileUrl($fileAccessToken, $n2nContext));
		}

		if (!$this->imageRecognized) {
			return $siFile;
		}

		$thumbImageFile = $this->buildThumb($file);

		if ($thumbImageFile === null) {
			return $siFile;
		}

		$thumbFile = $thumbImageFile->getFile();

		if ($thumbFile->getFileSource()->isHttpAccessible()) {
			$siFile->setThumbUrl($thumbFile->getFileSource()->getUrl());
		} else if ($tmpQualifiedName !== null) {
			$fileAccessToken = $n2nContext->lookup(GuiResourceRegistry::class)->registerFile(
					TmpFileManager::class,
					$file->getFileSource()->getQualifiedName(),
					SiFile::getThumbStrategy()->getImageDimension());
			$siFile->setThumbUrl(GuiResourceController::determineFileUrl($fileAccessToken, $n2nContext));
		} else {
			$fileAccessToken = $n2nContext->lookup(GuiResourceRegistry::class)->registerFile(
					$file->getFileSource()->getFileManagerName(),
					$file->getFileSource()->getQualifiedName(),
					SiFile::getThumbStrategy()->getImageDimension());
			$siFile->setThumbUrl(GuiResourceController::determineFileUrl($fileAccessToken, $n2nContext));
		}

		$siFile->setMimeType($thumbImageFile->getImageSource()->getMimeType());

		return $siFile;
	}

	function determineTmpQualifiedNameIfExists(File $file, N2nContext $n2nContext): ?string {
		if ($n2nContext->lookup(TmpFileManager::class)
				->containsSessionFile($file, $n2nContext->lookup(\n2n\web\http\HttpContext::class)->getSession())) {
			return $file->getFileSource()->getQualifiedName();
		}

		return null;
	}

	/**
	 * @param File $file
	 * @return ImageFile|null
	 */
	private function buildThumb(File $file): ?ImageFile {
		if (!$file->getFileSource()->getAffiliationEngine()->hasThumbSupport()) {
			return null;
		}

		$thumbStrategy = SiFile::getThumbStrategy();
		return (new ImageFile($file))->getOrCreateThumb($thumbStrategy);
	}
	
	function determineTmpFile(SiFileId $fileId, N2nContext $n2nContext): ?File {
		if ($fileId->getFileManagerName() !== TmpFileManager::class) {
			return null;
		}

		$tfm = $n2nContext->lookup(TmpFileManager::class);
		CastUtils::assertTrue($tfm instanceof TmpFileManager);

		$file = $tfm->getSessionFile($fileId->getQualifiedName(),
				$n2nContext->lookup(\n2n\web\http\HttpContext::class)->getSession());
		// Could be a FileId of unupdated but already save frontend entry. In this case there will be no matching
		// session file and the current field value should be returned.
		if ($file !== null) {
			return $file;
		}

		return null;
	}
		

//	function createFileUrl(Eiu $eiu, string $pid) {
//		return $eiu->frame()->getCmdUrl($this->thumbEiCmdPath)->extR(['file', $pid]);
//	}
//
//	/**
//	 * @param EiuFrame $eiuFrame
//	 * @param ImageDimension $imageDimension
//	 * @return \n2n\util\uri\Url
//	 */
//	function createThumbUrl(Eiu $eiu, ImageDimension $imageDimension) {
//		return $eiu->frame()->getCmdUrl($this->thumbEiCmdPath)
//				->extR(['thumb', $eiu->entry()->getPid()], ['imgDim' => $imageDimension->__toString()]);
//	}
//
//	/**
//	 * @param Eiu $eiu
//	 * @param string $qualifiedName
//	 * @return \n2n\util\uri\Url
//	 */
//	function createTmpUrl(Eiu $eiu, string $qualifiedName) {
//		return $eiu->frame()->getCmdUrl($this->thumbEiCmdPath)->extR(['tmp'], ['qn' => $qualifiedName]);
//	}
//
//	/**
//	 * @param Eiu $eiu
//	 * @param string $qualifiedName
//	 * @param ImageDimension $thumbImgDim
//	 * @return \n2n\util\uri\Url
//	 */
//	function createTmpThumbUrl(Eiu $eiu, string $qualifiedName, ImageDimension $thumbImgDim/*, ?ImageDimension $variationImgDim = null*/) {
//		$query = ['qn' => $qualifiedName, 'imgDim' => $thumbImgDim->__toString()];
//
//// 		if ($variationImgDim !== null) {
//// 			$query['variationImgDim'] = (string) $variationImgDim;
//// 		}
//
//		return $eiu->frame()->getCmdUrl($this->thumbEiCmdPath)->extR(['tmpthumb'], $query);
//	}
//
//	/**
//	 * @param File $file
//	 * @return boolean
//	 */
//	function isThumbCreationEnabled(File $file) {
//		if ($this->thumbEiCmdPath === null
//				|| !$file->getFileSource()->getAffiliationEngine()->hasThumbSupport()) return false;
//
//		if (!empty($this->extraImageDimensions)) return true;
//
//		$thumbEngine = $file->getFileSource()->getThumbManager();
//		switch ($this->imageDimensionsImportMode) {
//			case self::DIM_IMPORT_MODE_ALL:
//				return !empty($thumbEngine->getPossibleImageDimensions());
//			case self::DIM_IMPORT_MODE_USED_ONLY:
//				return !empty($thumbEngine->getUsedImageDimensions());
//			default:
//				return false;
//		}
//	}
//
//
//
//
//
//	public function setImageDimensionsImportMode(ImageDimensionsImportMode $imageDimensionsImportMode): void {
//		$this->imageDimensionsImportMode = $imageDimensionsImportMode;
//	}

}