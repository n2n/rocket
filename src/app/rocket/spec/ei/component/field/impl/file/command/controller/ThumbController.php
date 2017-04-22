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
namespace rocket\spec\ei\component\field\impl\file\command\controller;

use rocket\core\model\Breadcrumb;
use n2n\l10n\DynamicTextCollection;
use n2n\io\managed\img\ImageFile;
use rocket\core\model\RocketState;
use rocket\spec\ei\manage\ManageState;
use n2n\web\http\PageNotFoundException;
use rocket\spec\ei\component\field\impl\file\FileEiProp;
use n2n\web\http\controller\ControllerAdapter;
use rocket\spec\ei\component\command\impl\common\controller\PathUtils;
use rocket\spec\ei\component\field\impl\file\command\model\ThumbModel;
use n2n\web\http\controller\ParamQuery;
use n2n\reflection\CastUtils;
use n2n\io\managed\File;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\util\model\EiuCtrl;

class ThumbController extends ControllerAdapter {	
	private $fileEiProp;
	private $rocketState;
	private $eiCtrlUtils;
	private $dtc;
	
	public function prepare(ManageState $manageState, RocketState $rocketState, DynamicTextCollection $dtc, 
			EiuCtrl $eiCtrlUtils) {
		$this->rocketState = $rocketState;
		$this->eiCtrlUtils = $eiCtrlUtils;
		$this->dtc = $dtc;
	}
	
	public function setFileEiProp(FileEiProp $fileEiProp) {
		$this->fileEiProp = $fileEiProp;
	}
	
	public function index($idRep, ParamQuery $refPath) {
		$redirectUrl = $this->eiCtrlUtils->parseRefUrl($refPath);
		$eiMapping = $this->eiCtrlUtils->lookupEiMapping($idRep);
		
		// because ThumbEiCommand gets added always on a supreme EiThing
		if (!$this->fileEiProp->getEiEngine()->getEiType()->isObjectValid($eiMapping->getEiObject()->getLiveObject())) {
			throw new PageNotFoundException('');
		}

		$file = $eiMapping->getValue($this->fileEiProp);
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
			$this->refresh();
			return;
		}
		
		$this->applyBreadcrumbs($eiMapping->getEiObject());
				
		$this->forward('..\view\thumb.html', 
				array('thumbModel' => $thumbModel, 'cancelUrl' => $redirectUrl));
	}
	
	private function buildImageDimensions(File $file) {
		$imageDimensions = array();
		
		foreach ($this->fileEiProp->getExtraImageDimensions() as $imageDimension) {
			$imageDimensions[(string) $imageDimension] = $imageDimension;
		}
		
		$thumbEngine = $file->getFileSource()->getThumbManager();
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
		$eiFrame = $this->eiCtrlUtils->frame()->getEiFrame();
		
		if (!$eiFrame->isOverviewDisabled()) {
			$this->rocketState->addBreadcrumb($eiFrame->createOverviewBreadcrumb($this->getHttpContext()));
		}
		
		if (!$eiFrame->isDetailDisabled()) {
			$this->rocketState->addBreadcrumb($eiFrame->createDetailBreadcrumb($this->getHttpContext(), $eiObject));
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
		$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $this->fileEiProp->getLabelLstr()));
	}
}
