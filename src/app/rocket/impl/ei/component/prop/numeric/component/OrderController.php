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
namespace rocket\impl\ei\component\prop\numeric\component;

use rocket\impl\ei\component\prop\numeric\OrderEiProp;
use rocket\ei\manage\ManageState;
use n2n\web\http\controller\ControllerAdapter;
use rocket\ei\manage\util\model\EiuFrame;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\util\model\UnknownEntryException;
use n2n\web\http\controller\ParamGet;
use rocket\ei\manage\util\model\EiuCtrl;

class OrderController extends ControllerAdapter {	
	private $orderEiProp;
	private $utils;
	private $eiCtrl;
	
	private function _init(ManageState $manageState, EiuCtrl $eiCtrl) {
		$this->utils = new EiuFrame($manageState->peakEiFrame());
		$this->eiCtrl = $eiCtrl;
	}
	
	public function setOrderEiProp(OrderEiProp $orderEiProp) {
		$this->orderEiProp = $orderEiProp;
		$this->eiType = $orderEiProp->getEiMask()->getEiType();
	}
	
	public function doBefore($targetPid, ParamGet $pids, ParamGet $refPath) {
		$refUrl = $this->eiCtrl->parseRefUrl($refPath);
		
		foreach ($pids->toStringArrayOrReject() as $pid) {
			$this->move($pid, $targetPid, true);
		}
		
		$this->eiCtrl->redirectToReferer($refUrl);
	}
	
	public function doAfter($targetPid, ParamGet $pids, ParamGet $refPath) {
		$refUrl = $this->eiCtrl->parseRefUrl($refPath);
		
		foreach (array_reverse($pids->toStringArrayOrReject()) as $pid) {
			$this->move($pid, $targetPid, false);
		}
		
		$this->eiCtrl->redirectToReferer($refUrl);
	}
	
	private function move(string $pid, string $targetPid, bool $before) {
		if ($pid === $targetPid) return;
		
		$eiEntityObj = null;
		$targetEiEntityObj = null;
		
		try {
			$eiEntityObj = $this->utils->lookupEiEntityObj($this->utils->pidToId($pid));
			$targetEiEntityObj = $this->utils->lookupEiEntityObj($this->utils->pidToId($targetPid));
		} catch (UnknownEntryException $e) {
			return;
		} catch (\InvalidArgumentException $e) {
			return;
		}
		
		$entityProperty = $this->orderEiProp->getEntityProperty();
		$targetOrderIndex = $entityProperty->readValue($targetEiEntityObj->getEntityObj());
		if (!$before) {
			$targetOrderIndex++;
		}
		
		$em = $this->utils->getEiFrame()->getManageState()->getEntityManager();
		$criteria = $em->createCriteria();
		$criteria->select('eo')
				->from($entityProperty->getEntityModel()->getClass(), 'eo')
				->where()->match(CrIt::p('eo', $entityProperty), '>=', $targetOrderIndex)->endClause()
				->order(CrIt::p('eo', $entityProperty), 'ASC');
		
		$newOrderIndex = $targetOrderIndex + OrderEiProp::ORDER_INCREMENT;
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$entityProperty->writeValue($entityObj, $newOrderIndex += OrderEiProp::ORDER_INCREMENT);
		}
		
		$entityProperty->writeValue($eiEntityObj->getEntityObj(), $targetOrderIndex);
		$em->flush();
	}
}
