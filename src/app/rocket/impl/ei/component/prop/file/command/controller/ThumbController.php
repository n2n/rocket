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
namespace rocket\impl\ei\component\prop\file\command\controller;

use n2n\io\managed\File;
use n2n\io\managed\img\ImageDimension;
use n2n\io\managed\img\ImageFile;
use n2n\io\managed\impl\TmpFileManager;
use n2n\io\managed\impl\engine\QualifiedNameFormatException;
use n2n\l10n\DynamicTextCollection;
use n2n\web\http\BadRequestException;
use n2n\web\http\PageNotFoundException;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\ParamQuery;
use rocket\ei\util\EiuCtrl;
use rocket\impl\ei\component\prop\file\FileEiProp;
use rocket\ei\EiPropPath;
use rocket\impl\ei\component\prop\file\conf\ThumbResolver;

class ThumbController extends ControllerAdapter {	
// 	/**
// 	 * @var ThumbResolver
// 	 */
// 	private $thumbResolver;
	private $fileEiProp;
	private $eiuCtrl;
	private $dtc;
	
	public function prepare(DynamicTextCollection $dtc) {
		$this->eiuCtrl = EiuCtrl::from($this->cu());
		$this->dtc = $dtc;
	}
	
	function setEiPropPath(EiPropPath $fileEiProp) {
		$this->fileEiProp = $fileEiProp;
	}
	
// 	public function setThumbResolver(ThumbResolver $thumbResolver) {
// 		$this->thumbResolver = $thumbResolver;
// 	}
	
// 	public function index($pid, ParamQuery $refPath, ParamQuery $selected = null) {
// 		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
// 		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		
		
// 		// because ThumbEiCommand gets added always on a supreme EiEngine
// 		if (!$eiuEntry->isTypeOf($this->fileEiProp->getEiMask()->getEiType())) {
// 			throw new PageNotFoundException('');
// 		}

// 		$file = $eiuEntry->getValue($this->fileEiProp);
// 		if ($file === null) {
// 			throw new PageNotFoundException();
// 		}
		
// 		CastUtils::assertTrue($file instanceof File);
		
// 		$imageDimensions = null;
// 		if ($file === null || !$file->isValid() || !$file->getFileSource()->isImage()
// 				|| !$file->getFileSource()->getAffiliationEngine()->hasThumbSupport()
// 				|| empty($imageDimensions = $this->buildImageDimensions($file))) {
			
// 			$this->redirect($redirectUrl);
// 			return;
// 		}
		
// 		$thumbModel = new ThumbModel(new ImageFile($file), $imageDimensions);
		
// 		if ($this->dispatch($thumbModel, 'save')) {
// // 			$this->redirectToController(array($pid), array('refPath' => (string) $refPath, 
// // 					'selected' => $thumbModel->selectedStr));
// // 			return;
// 		}
		
// 		if (null !== $selected) {
// 			$thumbModel->selectedStr = (string) $selected;
// 		}
		
// 		$this->applyBreadcrumbs($eiuEntry->object()->getEiObject());
				
// 		$this->forward('..\view\thumb.html', 
// 				array('thumbModel' => $thumbModel, 'cancelUrl' => $redirectUrl));
// 	}
	
// 	private function buildImageDimensions(File $file) {
// 		$imageDimensions = array();
		
// 		foreach ($this->fileEiProp->getExtraImageDimensions() as $imageDimension) {
// 			$imageDimensions[(string) $imageDimension] = $imageDimension;
// 		}
		
// 		$thumbEngine = $file->getFileSource()->getThumbManager();
// 		ArgUtils::assertTrue($thumbEngine instanceof ThumbManager);
// 		$autoImageDimensions = array();
// 		switch ($this->fileEiProp->getImageDimensionImportMode()) {
// 			case FileEiProp::DIM_IMPORT_MODE_ALL:
// 				$autoImageDimensions = $thumbEngine->getPossibleImageDimensions(); 
// 				break;
// 			case FileEiProp::DIM_IMPORT_MODE_USED_ONLY:
// 				$autoImageDimensions = $thumbEngine->getUsedImageDimensions();
// 				break;
// 		}
		
// 		foreach ($autoImageDimensions as $autoImageDimension) {
// 			$imageDimensions[(string) $autoImageDimension] = $autoImageDimension;
// 		}
		
// 		return $imageDimensions;
// 	}
	
	function doFile($pid) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		
		$file = $eiuEntry->getValue($this->fileEiProp);
		if ($file === null) {
			throw new PageNotFoundException();
		}
		
		$this->sendFile($file);
	}
	
	function doThumb($pid, ParamQuery $imgDim) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		$file = $eiuEntry->getValue($this->fileEiProp);
		
		if ($file === null) {
			throw new PageNotFoundException();
		}
		
		$imageDimension = null;
		try {
			$imageDimension = ImageDimension::createFromString($imgDim->__toString());
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, 0, $e);
		}
		
		$thumbFile = null;
		try {
			$thumbFile = (new ImageFile($file))->getThumbFile($imageDimension);
		} catch (\Exception $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		if ($thumbFile === null) {
			throw new PageNotFoundException();
		}
		
		$this->sendFile($thumbFile);
	}
	
	/**
	 * @param string $qualifiedName
	 * @throws BadRequestException
	 * @return File|null
	 */
	private function lookupTmpFile(string $qualifiedName) {
		$tmpFileManager = $this->getN2nContext()->lookup(TmpFileManager::class);
		try {
			return $tmpFileManager->getSessionFile($qualifiedName, $this->getHttpContext()->getSession());
		} catch (QualifiedNameFormatException $e) {
			throw new BadRequestException(null, 0, $e);
		}
		
	}
	
	function doTmp(ParamQuery $qn) {
		$file = $this->lookupTmpFile((string) $qn);
		if ($file !== null) {
			$this->sendFile($file);
			return;
		}
		
		throw new PageNotFoundException();	
	}
	
	function doTmpThumb(ParamQuery $qn, ParamQuery $imgDim) {
		$file = $this->lookupTmpFile((string) $qn);
		if ($file === null || !$file->getFileSource()->getAffiliationEngine()->hasThumbSupport()) {
			throw new PageNotFoundException();
		}
		
		$imageDimension = null;
		try {
			$imageDimension = ImageDimension::createFromString((string) $imgDim);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, 0, $e);
		}
		
		$thumbFile = (new ImageFile($file))->getThumbFile($imageDimension);
		if ($thumbFile === null) {
			throw new PageNotFoundException();
		}
		
		$this->sendFile($thumbFile);
	}
}
