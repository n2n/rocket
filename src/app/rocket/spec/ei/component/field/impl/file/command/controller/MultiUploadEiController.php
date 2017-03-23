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

use n2n\web\http\controller\ControllerAdapter;
use rocket\core\model\RocketState;
use n2n\l10n\DynamicTextCollection;
use rocket\spec\ei\component\field\impl\file\MultiUploadFileEiField;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\util\ex\IllegalStateException;
use rocket\core\model\Breadcrumb;
use rocket\spec\ei\component\field\impl\file\FileEiField;
use rocket\spec\ei\manage\util\model\EiuCtrl;
use rocket\spec\ei\EiFieldPath;
use n2n\io\managed\impl\FileFactory;

class MultiUploadEiController extends ControllerAdapter {
	private $fileEiField;
	private $namingEiFieldPath;
	
	/**
	 * @var \rocket\spec\ei\component\field\impl\file\MultiUploadFileEiField
	 */
	public function setFileEiField(FileEiField $fileEiField) {
		$this->fileEiField = $fileEiField;
	}
	
	public function setNamingEiFieldPath(EiFieldPath $namingEiFieldPath = null) {
		$this->namingEiFieldPath = $namingEiFieldPath;
	}
	
	public function index(EiuCtrl $eiuCtrl, DynamicTextCollection $dtc) {
		$eiuCtrl->applyCommonBreadcrumbs(null, $dtc->translate('ei_impl_multi_upload_label'));
	
		$this->forward('..\view\multiupload.html', array('eiuFrame' => $eiuCtrl->frame()));
	}
	
	public function doUpload(EiuCtrl $eiuCtrl) {
		$file = null;
		foreach ($this->getRequest()->getUploadDefinitions() as $uploadDefinition) {
			$file = FileFactory::createFromUploadDefinition($uploadDefinition);
			break;
		}
		
		if (null === $file) return;
		
		$eiuFrame = $eiuCtrl->frame();
		$eiuEntry = $eiuFrame->entry($eiuFrame->createNewEiEntry());
		
		$eiuEntry->setValue($this->fileEiField, $file);
		if (null !== $this->namingEiFieldPath) {
			$prettyNameParts = preg_split('/(\.|-|_)/', $file->getOriginalName());
			array_pop($prettyNameParts);
			$eiuEntry->setValue($this->namingEiFieldPath, implode(' ', $prettyNameParts));
		}
		
		if (!$eiuEntry->getEiMapping()->save()) {
			throw new IllegalStateException();
		}
		
		$eiuFrame->em()->persist($eiuEntry->getLiveEntry()->getEntityObj());
	}
	
	private function applyBreadCrumbs() {
		$dtc = new DynamicTextCollection('rocket');
		$this->rocketState->addBreadcrumb(
				$this->eiFrame->createOverviewBreadcrumb($this->getHttpContext()));
		$this->rocketState->addBreadcrumb(new Breadcrumb($this->getRequest()->getCurrentControllerContextPath(), 
				$dtc->translate('ei_impl_multi_upload_label')));
	}
}