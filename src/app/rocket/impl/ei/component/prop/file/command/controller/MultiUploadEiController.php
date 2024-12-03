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

use n2n\web\http\controller\ControllerAdapter;
use n2n\l10n\DynamicTextCollection;
use rocket\impl\ei\component\prop\file\FileEiPropNature;
use rocket\op\util\OpuCtrl;
use n2n\io\managed\impl\FileFactory;
use n2n\web\http\BadRequestException;
use n2n\util\type\ArgUtils;

class MultiUploadEiController extends ControllerAdapter {
	const ORDER_NONE = null;
	const ORDER_FILE_NAME_ASC = 'file-name-asc';
	const ORDER_FILE_NAME_DESC = 'file-name-desc';
	
	private $fileModel;
	private $order;

	/**
	 * @param FileEiPropNature $fileEiProp
	 */
	public function setFileEiProp(FileEiPropNature $fileEiProp) {
		$this->fileModel = $fileEiProp;
	}
	
	public function setOrder(?string $order = null) {
		ArgUtils::valEnum($order, self::getOrders());
		$this->order = $order;
	}
	
	public function index(OpuCtrl $opuCtrl, DynamicTextCollection $dtc) {
		$opuCtrl->pushCurrentAsSirefBreadcrumb($dtc->translate('ei_impl_multi_upload_label'), true);
	
		$this->forward('..\view\multiupload.html', array('eiuFrame' => $opuCtrl->frame(), 'order' => $this->order));
	}
	
	public function doUpload(OpuCtrl $opuCtrl) {
		$file = null;
		foreach ($this->getRequest()->getUploadDefinitions() as $uploadDefinition) {
			$file = FileFactory::createFromUploadDefinition($uploadDefinition);
			break;
		}
		
		if (null === $file) return;
		
		$eiuFrame = $opuCtrl->frame();
		$eiuEntry = $eiuFrame->entry($eiuFrame->createNewEiObject());

		$eiuEntry->setValue($this->fileModel, $file);
		
		$namingEiPropPath = $this->fileModel->getNamingEiPropPath();
		if (null !== $namingEiPropPath) {
			$prettyNameParts = preg_split('/(\.|-|_)/', $file->getOriginalName());
			array_pop($prettyNameParts);
			$eiuEntry->setScalarValue($namingEiPropPath, implode(' ', $prettyNameParts));
		}
		
		if (!$eiuEntry->getEiEntry()->save()) {
			throw new BadRequestException();
		}
		
		$eiuFrame->em()->persist($eiuEntry->getEiEntityObj()->getEntityObj());
	}
	
//	private function applyBreadCrumbs() {
//		$dtc = new DynamicTextCollection('rocket');
//		$this->rocketState->addBreadcrumb(
//				$this->eiFrame->createOverviewBreadcrumb($this->getHttpContext()));
//		$this->rocketState->addBreadcrumb(new Breadcrumb($this->getRequest()->getCurrentControllerContextPath(),
//				$dtc->translate('ei_impl_multi_upload_label')));
//	}
	
	public static function getOrders() {
		return [self::ORDER_NONE, self::ORDER_FILE_NAME_ASC, self::ORDER_FILE_NAME_DESC];
	}
}
