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
use rocket\spec\ei\mask\UnknownEiMaskException;
use n2n\web\dispatch\map\InvalidPropertyExpressionException;
use n2n\web\dispatch\map\PropertyPath;
use rocket\spec\ei\component\CritmodFactory;
use rocket\spec\ei\manage\critmod\filter\impl\form\FilterFieldItemForm;
use n2n\impl\web\ui\view\html\AjahResponse;
use rocket\spec\ei\manage\critmod\filter\data\FilterItemData;
use n2n\util\config\Attributes;
use rocket\spec\ei\manage\critmod\filter\impl\form\FilterGroupForm;
use rocket\spec\ei\manage\critmod\filter\data\FilterGroupData;
use rocket\spec\ei\EiThing;
use rocket\spec\ei\manage\EiState;
use n2n\util\uri\Url;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\spec\ei\manage\critmod\filter\FilterDefinition;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\manage\critmod\filter\UnknownFilterFieldException;

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
	
	private function lookupEiThing(string $eiSpecId, string $eiMaskId = null): EiThing {
		try {
			$eiSpec = $this->specManager->getEiSpecById($eiSpecId);
			if ($eiMaskId !== null) {
				return $eiSpec->getEiMaskCollection()->getById($eiMaskId);
			} 
			
			return $eiSpec;
		} catch (UnknownSpecException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (UnknownEiMaskException $e) {
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
	
	public function doSimple(string $eiSpecId, string $eiMaskId = null, ParamQuery $filterFieldId, ParamQuery $propertyPath) {
		$eiThing = $this->lookupEiThing($eiSpecId, $eiMaskId);
		$propertyPath = $this->buildPropertyPath((string) $propertyPath);
		$filterFieldId = (string) $filterFieldId;
		$filterDefinition = (new CritmodFactory($eiThing->getEiEngine()->getEiFieldCollection(), 
						$eiThing->getEiEngine()->getEiModificatorCollection()))
				->createFilterDefinition($this->getN2nContext());
	
		$filterFieldItemForm = null;
		try {
			$filterFieldItemForm = new FilterFieldItemForm(new FilterItemData($filterFieldId, new Attributes()),
					$filterDefinition);
		} catch (UnknownFilterFieldException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	
		$this->send(new AjahResponse($this->createView('..\view\pseudoFilterFieldItemForm.html', array(
				'filterFieldItemForm' => $filterFieldItemForm, 'propertyPath' => $propertyPath))));
	}
	
	public function doAdv(string $eiSpecId, string $eiMaskId = null, ParamQuery $filterFieldId, ParamQuery $propertyPath) {
		$eiThing = $this->lookupEiThing($eiSpecId, $eiMaskId);
		$propertyPath = $this->buildPropertyPath((string) $propertyPath);
		$filterFieldId = (string) $filterFieldId;
		$eiMappingFilterDefinition = (new CritmodFactory($eiThing->getEiEngine()->getEiFieldCollection(), $eiThing->getEiEngine()->getEiModificatorCollection()))
				->createEiMappingFilterDefinition($this->getN2nContext());
		$filterFieldItemForm = null;
		try {
			$filterFieldItemForm = new FilterFieldItemForm(new FilterItemData($filterFieldId, new Attributes()), 
					$eiMappingFilterDefinition);
		} catch (UnknownFilterFieldException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		$this->send(new AjahResponse($this->createView('..\view\pseudoFilterFieldItemForm.html', array(
				'filterFieldItemForm' => $filterFieldItemForm, 'propertyPath' => $propertyPath))));
	}
	
	public function doGroup(string $eiSpecId, string $eiMaskId = null, ParamQuery $propertyPath) {
		$eiThing = $this->lookupEiThing($eiSpecId, $eiMaskId);
		$propertyPath = $this->buildPropertyPath((string) $propertyPath);
		
		$filterGroupForm = new FilterGroupForm(new FilterGroupData(), new FilterDefinition());
		
		$this->send(new AjahResponse($this->createView(
				'..\view\pseudoFilterGroupForm.html', 
				array('filterGroupForm' => $filterGroupForm, 'propertyPath' => $propertyPath))));
	}
	
	public static function buildFilterAjahHook(ScrRegistry $scrRegistry, EiMask $eiMask): FilterAjahHook {
		$baseUrl = $scrRegistry->registerSessionScrController(GlobalFilterFieldController::class);
		$eiSpecId = $eiMask->getEiEngine()->getEiSpec()->getId();
		$eiMaskId = $eiMask->getId();
		
		return new FilterAjahHook(
				$baseUrl->extR(array('simple', $eiSpecId, $eiMaskId)),
				$baseUrl->extR(array('group', $eiSpecId, $eiMaskId)));
	}
	
	public static function buildEiMappingFilterAjahHook(ScrRegistry $scrRegistry, string $eiSpecId, string $eiMaskId = null): FilterAjahHook {
		$baseUrl = $scrRegistry->registerSessionScrController(GlobalFilterFieldController::class);
		
		return new FilterAjahHook(
				$baseUrl->extR(array('adv', $eiSpecId, $eiMaskId)),
				$baseUrl->extR(array('group', $eiSpecId, $eiMaskId)));
	}
}
