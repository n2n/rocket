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

use n2n\persistence\orm\criteria\Criteria;
use rocket\spec\ei\component\field\impl\numeric\OrderEiField;
use rocket\spec\ei\manage\ManageState;
use n2n\http\controller\ControllerAdapter;
use rocket\spec\ei\manage\util\model\EiStateUtils;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\spec\ei\manage\util\model\UnknownEntryException;
use n2n\http\controller\ParamGet;
use rocket\spec\ei\component\command\impl\common\controller\ControllingUtils;
use rocket\spec\ei\component\command\impl\common\controller\EiCtrlUtils;

class OrderController extends ControllerAdapter {	
	private $orderEiField;
	private $utils;
	private $eiCtrlUtils;
	
	private function _init(ManageState $manageState, EiCtrlUtils $eiCtrlUtils) {
		$this->utils = new EiStateUtils($manageState->peakEiState());
		$this->eiCtrlUtils = $eiCtrlUtils;
	}
	
	public function setOrderEiField(OrderEiField $orderEiField) {
		$this->orderEiField = $orderEiField;
		$this->eiSpec = $orderEiField->getEiEngine()->getEiSpec();
	}
	
	public function doBefore($targetIdRep, ParamGet $idReps, ParamGet $refPath) {
		$refUrl = $this->eiCtrlUtils->parseRefUrl($refPath);
		
		foreach ($idReps->toStringArrayOrReject() as $idRep) {
			$this->move($idRep, $targetIdRep, true);
		}
		
		$this->redirect($refUrl);
	}
	
	public function doAfter($targetIdRep, ParamGet $idReps, ParamGet $refPath) {
		$refUrl = $this->eiCtrlUtils->parseRefUrl($refPath);
		
		foreach (array_reverse($idReps->toStringArrayOrReject()) as $idRep) {
			$this->move($idRep, $targetIdRep, false);
		}
		
		$this->redirect($refUrl);
	}
	
	private function move(string $idRep, string $targetIdRep, bool $before) {
		if ($idRep === $targetIdRep) return;
		
		$liveEntry = null;
		$targetLiveEntry = null;
		
		try {
			$liveEntry = $this->utils->lookupLiveEntryById($this->utils->idRepToId($idRep));
			$targetLiveEntry = $this->utils->lookupLiveEntryById($this->utils->idRepToId($targetIdRep));
		} catch (UnknownEntryException $e) {
			return;
		} catch (\InvalidArgumentException $e) {
			return;
		}
		
		$entityProperty = $this->orderEiField->getEntityProperty();
		$targetOrderIndex = $entityProperty->readValue($targetLiveEntry->getEntityObj());
		if (!$before) {
			$targetOrderIndex++;
		}
		
		$em = $this->utils->getEiState()->getManageState()->getEntityManager();
		$criteria = $em->createCriteria();
		$criteria->select('eo')
				->from($entityProperty->getEntityModel()->getClass(), 'eo')
				->where()->match(CrIt::p('eo', $entityProperty), '>=', $targetOrderIndex)->endClause()
				->order(CrIt::p('eo', $entityProperty), 'ASC');
		
		$newOrderIndex = $targetOrderIndex + OrderEiField::ORDER_INCREMENT;
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$entityProperty->writeValue($entityObj, $newOrderIndex += OrderEiField::ORDER_INCREMENT);
		}
		
		$entityProperty->writeValue($liveEntry->getEntityObj(), $targetOrderIndex);
		$em->flush();
	}
}
