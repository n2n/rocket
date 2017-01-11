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
namespace rocket\spec\ei\component\field\impl\numeric\component;

use rocket\spec\ei\component\modificator\impl\adapter\EiModificatorAdapter;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\mapping\OnWriteMappingListener;
use rocket\spec\ei\component\field\impl\numeric\OrderEiField;
use rocket\spec\ei\manage\critmod\sort\SortCriteriaConstraintGroup;
use rocket\spec\ei\manage\critmod\sort\SimpleSortConstraint;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;

class OrderEiModificator extends EiModificatorAdapter {
	
	private $eiField;
	
	public function __construct(OrderEiField $eiField) {
		$this->eiField = $eiField;
	}
	
	public function setupEiState(EiState $eiState) {
		$eiState->getCriteriaConstraintCollection()->add(CriteriaConstraint::TYPE_HARD_SORT,
				new SortCriteriaConstraintGroup(array(
						new SimpleSortConstraint(CrIt::p($this->eiField->getEntityProperty()), 'ASC'))));
	}
	
	public function setupEiMapping(EiState $eiState, EiMapping $ssm) {
		$eiField = $this->eiField;
		$ssm->registerListener(new OnWriteMappingListener(function() use ($eiState, $ssm, $eiField) {
			$orderIndex = $ssm->getValue($eiField);
			
			if (mb_strlen($orderIndex)) return;
			
			$entityProperty = $eiField->getEntityProperty();
			
			$em = $eiState->getManageState()->getEntityManager();
			$criteria = $em->createCriteria()
					->select(CrIt::f('MAX', CrIt::p('eo', $entityProperty)))
					->from($entityProperty->getEntityModel()->getClass(), 'eo');
			
			$ssm->setValue($eiField, $criteria->toQuery()->fetchSingle() + OrderEiField::ORDER_INCREMENT);
		}));
	}
}
