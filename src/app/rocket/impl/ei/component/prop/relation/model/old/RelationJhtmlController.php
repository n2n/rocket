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
namespace rocket\impl\ei\component\prop\relation\command;

use rocket\impl\ei\component\command\common\controller\OverviewJhtmlController;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\ParamQuery;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\dispatch\map\InvalidPropertyExpressionException;
use rocket\impl\ei\component\prop\relation\model\mag\MappingForm;
use n2n\web\http\BadRequestException;
use n2n\util\uri\Url;
use rocket\ei\util\EiuCtrl;
use n2n\impl\web\ui\view\jhtml\JhtmlResponse;
use rocket\ei\manage\frame\Boundry;

class RelationJhtmlController extends ControllerAdapter {
	private $eiuCtrl;	
	
	public function prepare(EiuCtrl $eiCtrlUtil) {
		$this->eiuCtrl = $eiCtrlUtil;
	}
		
	public function doSelect(OverviewJhtmlController $delegateController, array $delegateCmds = array()) {
		$this->delegate($delegateController);
	}
	
	public function doNewMappingForm(ParamQuery $propertyPath, ParamQuery $draft, ParamQuery $chooseableEiTypeIds = null,
			ParamQuery $grouped = null) {
		try {
			$propertyPath = PropertyPath::createFromPropertyExpression((string) $propertyPath);
		} catch (InvalidPropertyExpressionException $e) {
			throw new BadRequestException(null, null, $e);
		}
		
		$allowedEiTypeIds = $chooseableEiTypeIds === null ? null : $chooseableEiTypeIds->toStringArrayOrReject();
		
		$mappingForm = null;
		try {
			$eiFrameUtils = $this->eiuCtrl->frame();
			$mappingForm = new MappingForm($eiFrameUtils->getGenericLabel(), $eiFrameUtils->getGenericIconType(), null,  
					$eiFrameUtils->newEntryForm($draft->toBool(), null, null, $allowedEiTypeIds));
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
		
		$view = $this->createView('\rocket\impl\ei\component\prop\relation\view\pseudoMappingForm.html',
				array('mappingForm' => $mappingForm, 'propertyPath' => $propertyPath,
						'grouped' => ($grouped !== null ? $grouped->toBool() : true)));
		
		$this->send(JhtmlResponse::view($view));
	}
	
	public function doCopyMappingForm(ParamQuery $propertyPath, ParamQuery $pid = null, ParamQuery $draftId = null,
			array $chooseableEiTypeIds = null, ParamQuery $grouped = null) {
		try {
			$propertyPath = PropertyPath::createFromPropertyExpression((string) $propertyPath);
		} catch (InvalidPropertyExpressionException $e) {
			throw new BadRequestException(null, null, $e);
		}
		
		$allowedEiTypeIds = $chooseableEiTypeIds === null ? null : $chooseableEiTypeIds->toStringArrayOrReject();
		
		if ($pid === null) {
			throw new BadRequestException();
		}
		
		$eiuEntry = $this->eiuCtrl->lookupEntry((string) $pid, Boundry::NON_SECURITY_TYPES);
		
		$mappingForm = null;
		try {
			$eiuFrame = $this->eiuCtrl->frame();
			
			$eiuEntryForm = $eiuFrame->newEntryForm(false, $eiuEntry, null, $allowedEiTypeIds);
			$mappingForm = new MappingForm($eiuFrame->getGenericLabel(), $eiuFrame->getGenericIconType(), null,
					$eiuEntryForm);
			
			$eiTypeId = $eiuEntry->getEiType()->getId();
			if ($eiuEntryForm->containsEiTypeId($eiTypeId)) {
				$eiuEntryForm->setChosenId($eiTypeId);
			}
			
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
		
		$view = $this->createView('\rocket\impl\ei\component\prop\relation\view\pseudoMappingForm.html',
				array('mappingForm' => $mappingForm, 'propertyPath' => $propertyPath,
						'grouped' => ($grouped !== null ? $grouped->toBool() : true)));
		
		$this->send(JhtmlResponse::view($view));
	}
	
	public static function buildNewFormUrl(Url $contextUrl, bool $draft): Url {
		return $contextUrl->extR(null, array('draft' => (bool) $draft));
	}
	
	public static function buildSelectToolsUrl(Url $contextUrl): Url {
		return OverviewJhtmlController::buildToolsAjahUrl($contextUrl->extR('select'));
	}
		
// 	public static function buildSelectAjahHook(Url $contextUrl): OverviewAjahHook {
// 		return OverviewJhtmlController::buildAjahHook($contextUrl->extR('select'), 
// 				OverviewJhtmlController::genStateKey());
// 	}
}
