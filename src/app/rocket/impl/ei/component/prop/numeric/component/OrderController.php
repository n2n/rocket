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
use n2n\web\http\controller\ControllerAdapter;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\web\http\controller\ParamGet;
use rocket\ei\util\EiuCtrl;
use rocket\ajah\JhtmlEvent;

class OrderController extends ControllerAdapter {	
	private $orderEiProp;
	private $eiuCtrl;
	
	private function _init(EiuCtrl $eiCtrl) {
		$this->eiuCtrl = $eiCtrl;
	}
	
	public function setOrderEiProp(OrderEiProp $orderEiProp) {
		$this->orderEiProp = $orderEiProp;
		$this->eiType = $orderEiProp->getEiMask()->getEiType();
	}
	
	public function doBefore($targetPid, ParamGet $pids, ParamGet $refPath) {
		$refUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
		foreach ($pids->toStringArrayOrReject() as $pid) {
			$this->move($pid, $targetPid, true);
		}
		
		$this->eiuCtrl->redirectToReferer($refUrl, JhtmlEvent::ei()->noAutoEvents());
	}
	
	public function doAfter($targetPid, ParamGet $pids, ParamGet $refPath) {
		$refUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
		foreach (array_reverse($pids->toStringArrayOrReject()) as $pid) {
			$this->move($pid, $targetPid, false);
		}
		
		$this->eiuCtrl->redirectToReferer($refUrl, JhtmlEvent::ei()->noAutoEvents());
	}
	
	private function move(string $pid, string $targetPid, bool $before) {
		if ($pid === $targetPid) return;
		
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		$targetEiuEntity = $this->eiuCtrl->lookupEntry($targetPid);
		
		$entityProperty = $this->orderEiProp->getEntityProperty();
		$targetOrderIndex = $entityProperty->readValue($targetEiuEntity->getEntityObj());
		if (!$before) {
			$targetOrderIndex++;
		}
		
		$em = $this->eiuCtrl->frame()->em();
		$criteria = $em->createCriteria();
		$criteria->select('eo')
				->from($entityProperty->getEntityModel()->getClass(), 'eo')
				->where()->match(CrIt::p('eo', $entityProperty), '>=', $targetOrderIndex)->endClause()
				->order(CrIt::p('eo', $entityProperty), 'ASC');
		
		$newOrderIndex = $targetOrderIndex + OrderEiProp::ORDER_INCREMENT;
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$entityProperty->writeValue($entityObj, $newOrderIndex += OrderEiProp::ORDER_INCREMENT);
		}
		
		$entityProperty->writeValue($eiuEntry->getEntityObj(), $targetOrderIndex);
		$em->flush();
	}
}
