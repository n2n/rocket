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
namespace rocket\impl\ei\component\prop\bool\command;

use rocket\impl\ei\component\prop\bool\OnlineEiProp;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\ForbiddenException;
use rocket\ei\util\model\EiuCtrl;
use rocket\ajah\JhtmlEvent;
use rocket\ei\util\model\Eiu;

class OnlineController extends ControllerAdapter {
	private $onlineEiProp;
	private $onlineEiCommand;
	private $eiuCtrl;
	
	public function prepare(EiuCtrl $eiCtrl) {
		$this->eiuCtrl = $eiCtrl;
	}
	
	public function setOnlineEiProp(OnlineEiProp $onlineEiProp) {
		$this->onlineEiProp = $onlineEiProp;
	}
	
	public function setOnlineEiCommand(OnlineEiCommand $onlineEiCommand) {
		$this->onlineEiCommand = $onlineEiCommand;
	}
	
	public function doOnline($pid) {
		$this->setStatus(true, $pid);
	}
	
	public function doOffline($pid) {
		$this->setStatus(false, $pid);
	}
	
	private function setStatus($status, $pid) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		$eiuEntry->setValue($this->onlineEiProp, $status);		
		if (!$eiuEntry->getEiEntry()->save()) {
			throw new ForbiddenException();
		}
		
		$this->eiuCtrl->redirectToReferer($this->eiuCtrl->buildRedirectUrl($eiuEntry), 
				JhtmlEvent::ei()->eiObjectChanged($eiuEntry)
						->controlSwaped($this->onlineEiCommand->createEntryControl(new Eiu($eiuEntry))));
	}
}
