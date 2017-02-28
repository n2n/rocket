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
namespace rocket\spec\ei\component\field\impl\bool\command;

use rocket\spec\ei\component\field\impl\bool\OnlineEiField;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\ForbiddenException;
use n2n\impl\web\ui\view\json\JsonResponse;
use rocket\spec\ei\manage\util\model\EiuCtrl;

class OnlineController extends ControllerAdapter {
	const ACTION_OFFLINE = 'offline';
	
	private $onlineEiField;
	private $eiCtrlUtils;
	
	public function prepare(EiuCtrl $eiCtrlUtils) {
		$this->eiCtrlUtils = $eiCtrlUtils;
	}
	
	public function setOnlineEiField(OnlineEiField $onlineEiField) {
		$this->onlineEiField = $onlineEiField;
	}
	
	public function doOnline($idRep) {
		$this->setStatus(true, $idRep);
	}
	
	public function doOffline($idRep) {
		$this->setStatus(false, $idRep);
	}
	
	private function setStatus($status, $idRep) {
		$eiMapping = $this->eiCtrlUtils->lookupEiMapping($idRep);
		$eiMapping->setValue($this->onlineEiField, $status);		
		if (!$eiMapping->save()) {
			throw new ForbiddenException();
		}
		
		$this->send(new JsonResponse(array('status' => 'ok')));
	}
}
