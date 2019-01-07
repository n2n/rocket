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

use rocket\core\model\Breadcrumb;
use n2n\l10n\DynamicTextCollection;
use n2n\io\managed\img\ImageFile;
use n2n\web\http\PageNotFoundException;
use rocket\impl\ei\component\prop\file\FileEiProp;
use n2n\web\http\controller\ControllerAdapter;
use rocket\impl\ei\component\command\common\controller\PathUtils;
use rocket\impl\ei\component\prop\file\command\model\ThumbModel;
use n2n\web\http\controller\ParamQuery;
use n2n\util\type\CastUtils;
use n2n\io\managed\File;
use rocket\ei\manage\EiObject;
use rocket\ei\util\EiuCtrl;
use n2n\util\type\ArgUtils;
use n2n\io\managed\ThumbManager;

class ThumbController extends ControllerAdapter {	
	private $fileEiProp;
	private $eiuCtrl;
	private $dtc;
	
	public function prepare(DynamicTextCollection $dtc, EiuCtrl $eiuCtrl) {
		$this->eiuCtrl = $eiuCtrl;
		$this->dtc = $dtc;
	}
	
	public function setFileEiProp(FileEiProp $fileEiProp) {
		$this->fileEiProp = $fileEiProp;
	}
	
	public function index($pid, ParamQuery $refPath, ParamQuery $selected = null) {
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		
		
		// because ThumbEiCommand gets added always on a supreme EiEngine
		if (!$eiuEntry->isTypeOf($this->fileEiProp->getEiMask()->getEiType())) {
			throw new PageNotFoundException('');
		}

		$file = $eiuEntry->getValue($this->fileEiProp);
		if ($file === null) {
			throw new PageNotFoundException();
		}
		
		CastUtils::assertTrue($file instanceof File);
		
		$imageDimensions = null;
		if ($file === null || !$file->isValid() || !$file->getFileSource()->isImage()
				|| !$file->getFileSource()->getVariationEngine()->hasThumbSupport()
				|| empty($imageDimensions = $this->buildImageDimensions($file))) {
			
			$this->redirect($redirectUrl);
			return;
		}
		
		$thumbModel = new ThumbModel(new ImageFile($file), $imageDimensions);
		
		if ($this->dispatch($thumbModel, 'save')) {
// 			$this->redirectToController(array($pid), array('refPath' => (string) $refPath, 
// 					'selected' => $thumbModel->selectedStr));
// 			return;
		}
		
		if (null !== $selected) {
			$thumbModel->selectedStr = (string) $selected;
		}
		
		$this->applyBreadcrumbs($eiuEntry->object()->getEiObject());
				
		$this->forward('..\view\thumb.html', 
				array('thumbModel' => $thumbModel, 'cancelUrl' => $redirectUrl));
	}
	
	private function buildImageDimensions(File $file) {
		$imageDimensions = array();
		
		foreach ($this->fileEiProp->getExtraImageDimensions() as $imageDimension) {
			$imageDimensions[(string) $imageDimension] = $imageDimension;
		}
		
		$thumbEngine = $file->getFileSource()->getThumbManager();
		ArgUtils::assertTrue($thumbEngine instanceof ThumbManager);
		$autoImageDimensions = array();
		switch ($this->fileEiProp->getImageDimensionImportMode()) {
			case FileEiProp::DIM_IMPORT_MODE_ALL:
				$autoImageDimensions = $thumbEngine->getPossibleImageDimensions(); 
				break;
			case FileEiProp::DIM_IMPORT_MODE_USED_ONLY:
				$autoImageDimensions = $thumbEngine->getUsedImageDimensions();
				break;
		}
		
		foreach ($autoImageDimensions as $autoImageDimension) {
			$imageDimensions[(string) $autoImageDimension] = $autoImageDimension;
		}
		
		return $imageDimensions;
	}
	
	
	private function applyBreadcrumbs(EiObject $eiObject) {
		$eiFrame = $this->eiuCtrl->frame()->getEiFrame();
		
		if (!$eiFrame->isOverviewDisabled()) {
			$this->eiuCtrl->applyBreadcrumbs($eiFrame->createOverviewBreadcrumb($this->getHttpContext()));
		}
		
		if (!$eiFrame->isDetailDisabled()) {
			$this->eiuCtrl->applyBreadcrumbs($eiFrame->createDetailBreadcrumb($this->getHttpContext(), $eiObject));
		}
		
// 		if ($eiObject->isDraft()) {			
// 			$breadcrumbPath = $request->getControllerContextPath($eiFrame->getControllerContext(),
// 					$this->eiType->getEntryDetailPathExt($eiObject->toEntryNavPoint(
// 							$eiFrame->getPreviewType())->copy(false, true)));
// 			$breadcrumbLabel = $eiObject->getDraft()->getName();
// 			$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
// 		}
		
// 		if ($eiObject->hasTranslation()) {
// 			$breadcrumbPath = $request->getControllerContextPath($eiFrame->getControllerContext(),
// 					$this->eiType->getEntryDetailPathExt($eiObject->toEntryNavPoint(
// 							$eiFrame->getPreviewType())->copy(true, true)));
// 			$breadcrumbLabel = $this->dtc->translate('ei_impl_translation_detail_bradcrumb' ,
// 			$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
// 		}
		
		$breadcrumbPath = $this->getHttpContext()->getControllerContextPath($eiFrame->getControllerContext())
				->ext(PathUtils::createPathExtFromEntryNavPoint($this->fileEiProp->getThumbEiCommand(), 
						$eiObject->toEntryNavPoint()));
			$this->eiuCtrl->applyBreadcrumbs(new Breadcrumb($breadcrumbPath, $this->fileEiProp->getLabelLstr()));
	}
	
	public function doPreview($pid) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		
		$file = $eiuEntry->getValue($this->fileEiProp);
		if ($file === null) {
			throw new PageNotFoundException();
		}
		
		$this->sendFile($file);
	}
}
