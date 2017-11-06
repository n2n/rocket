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

use rocket\spec\ei\component\field\impl\bool\OnlineEiProp;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\ForbiddenException;
use n2n\impl\web\ui\view\json\JsonResponse;
use rocket\spec\ei\manage\util\model\EiuCtrl;
use rocket\ajah\AjahEvent;

class OnlineController extends ControllerAdapter {
	private $onlineEiProp;
	private $eiuCtrl;
	
	public function prepare(EiuCtrl $eiCtrl) {
		$this->eiuCtrl = $eiCtrl;
	}
	
	public function setOnlineEiProp(OnlineEiProp $onlineEiProp) {
		$this->onlineEiProp = $onlineEiProp;
	}
	
	public function doOnline($idRep) {
		$this->setStatus(true, $idRep);
	}
	
	public function doOffline($idRep) {
		$this->setStatus(false, $idRep);
	}
	
	private function setStatus($status, $idRep) {
		$eiEntry = $this->eiuCtrl->lookupEiEntry($idRep);
		$eiEntry->setValue($this->onlineEiProp, $status);		
		if (!$eiEntry->save()) {
			throw new ForbiddenException();
		}
		
		$this->eiuCtrl->redirectBack($this->eiuCtrl->buildRedirectUrl($eiEntry), 
				AjahEvent::ei()->eiObjectChanged($eiEntry));
		$this->send(new JsonResponse(array('status' => 'ok')));
	}
}
