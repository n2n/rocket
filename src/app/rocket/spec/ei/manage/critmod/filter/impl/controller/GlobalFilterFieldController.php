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
namespace rocket\spec\ei\manage\critmod\filter\impl\controller;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\impl\ScrController;
use n2n\web\http\controller\ParamQuery;
use rocket\core\model\Rocket;
use rocket\user\model\LoginContext;
use rocket\spec\config\UnknownSpecException;
use n2n\web\http\PageNotFoundException;
use rocket\spec\ei\mask\UnknownEiMaskExtensionException;
use n2n\web\dispatch\map\InvalidPropertyExpressionException;
use n2n\web\dispatch\map\PropertyPath;
use rocket\spec\ei\component\CritmodFactory;
use rocket\spec\ei\manage\critmod\filter\impl\form\FilterFieldItemForm;
use rocket\spec\ei\manage\critmod\filter\data\FilterItemData;
use n2n\util\config\Attributes;
use rocket\spec\ei\manage\critmod\filter\impl\form\FilterGroupForm;
use rocket\spec\ei\manage\critmod\filter\data\FilterGroupData;
use rocket\spec\ei\EiEngineModel;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\spec\ei\manage\critmod\filter\FilterDefinition;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\manage\critmod\filter\UnknownFilterFieldException;
use n2n\impl\web\ui\view\jhtml\JhtmlResponse;

class GlobalFilterFieldController extends ControllerAdapter implements ScrController {
	private $specManager;
	private $loginContext;
	
	private function _init(Rocket $rocket, LoginContext $loginContext) {
		$this->specManager = $rocket->getSpecManager();
		$this->loginContext = $loginContext;
	}
	/**
	 * {@inheritDoc}
	 * @see \n2n\web\http\controller\impl\ScrController::isValid()
	 */
	public function isValid(): bool {
		return $this->loginContext->hasCurrentUser()
				&& $this->loginContext->getCurrentUser()->isAdmin();
	}
	
	private function lookupEiThing(string $eiTypeId, string $eiMaskId = null): EiEngineModel {
		try {
			$eiType = $this->specManager->getEiTypeById($eiTypeId);
			if ($eiMaskId !== null) {
				return $eiType->getEiMaskCollection()->getById($eiMaskId);
			} 
			
			return $eiType;
		} catch (UnknownSpecException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (UnknownEiMaskExtensionException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	}
	
	private function buildPropertyPath(string $str): PropertyPath {
		try {
			return PropertyPath::createFromPropertyExpression($str);
		} catch (InvalidPropertyExpressionException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	}
	
	public function doSimple(string $eiTypeId, string $eiMaskId = null, ParamQuery $filterFieldId, ParamQuery $propertyPath) {
		$eiThing = $this->lookupEiThing($eiTypeId, $eiMaskId);
		$propertyPath = $this->buildPropertyPath((string) $propertyPath);
		$filterFieldId = (string) $filterFieldId;
		$filterDefinition = (new CritmodFactory($eiThing->getEiEngine()->getEiPropCollection(), 
						$eiThing->getEiEngine()->getEiModificatorCollection()))
				->createFilterDefinition($this->getN2nContext());
	
		$filterFieldItemForm = null;
		try {
			$filterFieldItemForm = new FilterFieldItemForm(new FilterItemData($filterFieldId, new Attributes()),
					$filterDefinition);
		} catch (UnknownFilterFieldException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	
		$this->send(JhtmlResponse::view($this->createView('..\view\pseudoFilterFieldItemForm.html', array(
				'filterFieldItemForm' => $filterFieldItemForm, 'propertyPath' => $propertyPath))));
	}
	
	public function doAdv(string $eiTypeId, string $eiMaskId = null, ParamQuery $filterFieldId, ParamQuery $propertyPath) {
		$eiThing = $this->lookupEiThing($eiTypeId, $eiMaskId);
		$propertyPath = $this->buildPropertyPath((string) $propertyPath);
		$filterFieldId = (string) $filterFieldId;
		$eiEntryFilterDefinition = (new CritmodFactory($eiThing->getEiEngine()->getEiPropCollection(), $eiThing->getEiEngine()->getEiModificatorCollection()))
				->createEiEntryFilterDefinition($this->getN2nContext());
		$filterFieldItemForm = null;
		try {
			$filterFieldItemForm = new FilterFieldItemForm(new FilterItemData($filterFieldId, new Attributes()), 
					$eiEntryFilterDefinition);
		} catch (UnknownFilterFieldException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		$this->send(JhtmlResponse::view($this->createView('..\view\pseudoFilterFieldItemForm.html', array(
				'filterFieldItemForm' => $filterFieldItemForm, 'propertyPath' => $propertyPath))));
	}
	
	public function doGroup(string $eiTypeId, string $eiMaskId = null, ParamQuery $propertyPath) {
		$eiThing = $this->lookupEiThing($eiTypeId, $eiMaskId);
		$propertyPath = $this->buildPropertyPath((string) $propertyPath);
		
		$filterGroupForm = new FilterGroupForm(new FilterGroupData(), new FilterDefinition());
		
		$this->send(JhtmlResponse::view($this->createView(
				'..\view\pseudoFilterGroupForm.html', 
				array('filterGroupForm' => $filterGroupForm, 'propertyPath' => $propertyPath))));
	}
	
	public static function buildFilterAjahHook(ScrRegistry $scrRegistry, EiMask $eiMask): FilterAjahHook {
		$baseUrl = $scrRegistry->registerSessionScrController(GlobalFilterFieldController::class);
		$eiTypeId = $eiMask->getEiEngine()->getEiType()->getId();
		$eiMaskId = ($eiMask->hasId() ? $eiMask->getId() : null);
		
		return new FilterAjahHook(
				$baseUrl->extR(array('simple', $eiTypeId, $eiMaskId)),
				$baseUrl->extR(array('group', $eiTypeId, $eiMaskId)));
	}
	
	public static function buildEiEntryFilterAjahHook(ScrRegistry $scrRegistry, string $eiTypeId, string $eiMaskId = null): FilterAjahHook {
		$baseUrl = $scrRegistry->registerSessionScrController(GlobalFilterFieldController::class);
		
		return new FilterAjahHook(
				$baseUrl->extR(array('adv', $eiTypeId, $eiMaskId)),
				$baseUrl->extR(array('group', $eiTypeId, $eiMaskId)));
	}
}
