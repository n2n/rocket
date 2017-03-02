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
use n2n\core\N2N;
use n2n\io\fs\UploadedFileManager;
use n2n\web\dispatch\map\PropertyPath;
use rocket\spec\ei\manage\ManageState;
use rocket\core\model\RocketState;
use rocket\spec\ei\manage\EiSelection;
use n2n\l10n\DynamicTextCollection;
use rocket\spec\ei\manage\control\EntryControlComponent;
use rocket\spec\ei\component\field\impl\file\MultiUploadFileEiField;
use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\manage\mapping\MappingValidationResult;
use n2n\util\ex\IllegalStateException;
use rocket\core\model\Breadcrumb;

class MultiUploadScriptController extends ControllerAdapter {
	
	const ACTION_UPLOAD = 'upload';
	
	private $eiFrame;
	private $rocketState;
	private $utils;
	private $eiField;
	/**
	 * @var \rocket\spec\ei\component\field\impl\file\MultiUploadFileEiField
	 */
	private function _init(ManageState $manageState, RocketState $rocketState) {
		$this->eiFrame = $manageState->peakEiFrame();
		$this->rocketState = $rocketState;
		$this->utils = new EiuFrame($manageState->peakEiFrame());
	}
	
	public function setEiField(MultiUploadFileEiField $eiField) {
		$this->eiField = $eiField;
	}
	
	public function index() {
		$tx = N2N::createTransaction(true);
		if ($this->eiFrame->getExecutedEiCommand() instanceof EntryControlComponent) {
			$this->eiFrame->setEiSelection(new EiSelection($galleryId, $gallery));
		}
		$this->applyBreadCrumbs();
		$tx->commit();
		$this->forward('\rocket\spec\ei\component\field\impl\file\command\view\multiupload.html', 
				array('eiFrame' => $this->eiFrame));
	}
	
	public function doUpload(UploadedFileManager $ufm) {
		$file = $ufm->get(new PropertyPath(array('upl')));
		if (null === $file) return;
		$tx = N2N::createTransaction();
		$entryForm = $this->utils->createNewEntryForm();
		$entryManager = $this->utils->createEntryManager();
		
		$eiMapping = $entryForm->buildEiMapping();
		
		$eiMapping->setValue($this->eiField->getId(), $file);
		if (null !== ($referencedNamePropertyId = $this->eiField->getReferencedNamePropertyId())) {
			$prettyNameParts = preg_split('/(\.|-|_)/', $file->getOriginalName());
			array_pop($prettyNameParts);
			$eiMapping->setValue($referencedNamePropertyId, implode(' ', $prettyNameParts));
		}
		$entryManager->create($eiMapping);
		
		$mappingValidationResult = new MappingValidationResult();
		if (!$eiMapping->save($mappingValidationResult)) {
			//$messageContainer->addAll($mappingValidationResult->getMessages());
			throw IllegalStateException::createDefault();
		}
		$eiSelection = $eiMapping->getEiSelection();
		$em = $this->eiFrame->getEntityManager();
		$em->persist($eiSelection->getEntityObj());
		$tx->commit();
	}
	
	private function applyBreadCrumbs() {
		$dtc = new DynamicTextCollection('rocket');
		$this->rocketState->addBreadcrumb(
				$this->eiFrame->createOverviewBreadcrumb($this->getHttpContext()));
		$this->rocketState->addBreadcrumb(new Breadcrumb($this->getRequest()->getCurrentControllerContextPath(), 
				$dtc->translate('ei_impl_multi_upload_label')));
	}
}
