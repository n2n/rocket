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
use n2n\web\http\controller\ParamQuery;
use n2n\web\http\PageNotFoundException;
use n2n\web\dispatch\map\InvalidPropertyExpressionException;
use n2n\web\dispatch\map\PropertyPath;
use rocket\spec\ei\manage\critmod\filter\impl\form\FilterFieldItemForm;
use rocket\spec\ei\manage\critmod\filter\data\FilterItemData;
use n2n\util\config\Attributes;
use rocket\spec\ei\manage\critmod\filter\impl\form\FilterGroupForm;
use rocket\spec\ei\manage\critmod\filter\data\FilterGroupData;
use n2n\util\uri\Url;
use rocket\spec\ei\manage\critmod\filter\FilterDefinition;
use rocket\spec\ei\manage\ManageState;
use rocket\spec\ei\manage\critmod\filter\UnknownFilterFieldException;
use n2n\impl\web\ui\view\jhtml\JhtmlResponse;

class FilterFieldController extends ControllerAdapter  {
	private $eiFrame;
	
	public function prepare(ManageState $manageState) {
		if ($manageState->isActive()) {
			$this->eiFrame = $manageState->peakEiFrame();
			return;
		}
		
		throw new PageNotFoundException();
	}
	
	private function buildPropertyPath(string $str): PropertyPath {
		try {
			return PropertyPath::createFromPropertyExpression($str);
		} catch (InvalidPropertyExpressionException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	}
	
	public function doSimple(ParamQuery $filterFieldId, ParamQuery $propertyPath) {
		$propertyPath = $this->buildPropertyPath((string) $propertyPath);
		$filterFieldId = (string) $filterFieldId;
		
		$eiMask = $this->eiFrame->getContextEiMask();
		$filterDefinition = $eiMask->getEiEngine()->createManagedFilterDefinition($this->eiFrame);
	
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
	
	public function doGroup(ParamQuery $propertyPath) {
		$propertyPath = $this->buildPropertyPath((string) $propertyPath);
	
		$filterGroupForm = new FilterGroupForm(new FilterGroupData(), new FilterDefinition());
	
		$this->send(JhtmlResponse::view($this->createView(
				'..\view\pseudoFilterGroupForm.html', 
				array('filterGroupForm' => $filterGroupForm, 'propertyPath' => $propertyPath))));
	}
	
	public static function buildFilterAjahHook(Url $baseUrl): FilterAjahHook {
		return new FilterAjahHook($baseUrl->extR(array('simple')), $baseUrl->extR(array('group')));
	}
}
