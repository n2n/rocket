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
namespace rocket\spec\ei\component\command\impl\tree\controller;

use rocket\spec\ei\manage\ManageState;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\ParamGet;
use rocket\spec\ei\manage\util\model\UnknownEntryException;
use n2n\persistence\orm\util\NestedSetUtils;
use rocket\spec\ei\manage\util\model\EiuCtrl;

class TreeMoveController extends ControllerAdapter {
	private $eiCtrlUtils;

	public function prepare(ManageState $manageState) {
		$this->eiCtrlUtils = EiuCtrl::from($this->getHttpContext());
	}

	public function doChild($targetIdRep, ParamGet $idReps, ParamGet $refPath) {
		$refUrl = $this->eiCtrlUtils->parseRefUrl($refPath);
		
		foreach ($idReps->toStringArrayOrReject() as $idRep) {
			$this->move($idRep, $targetIdRep);
		}
		
		$this->redirect($refUrl);
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

	private function move(string $idRep, string $targetIdRep, bool $before = null) {
		if ($idRep === $targetIdRep) return;

		$eiUtils = $this->eiCtrlUtils->frame();
		
		$nestedSetStrategy = $eiUtils->getNestedSetStrategy();
		if ($nestedSetStrategy === null) return;
		
		$liveEntry = null;
		$targetLiveEntry = null;

		try {
			$liveEntry = $eiUtils->lookupLiveEntryById($eiUtils->idRepToId($idRep));
			$targetLiveEntry = $eiUtils->lookupLiveEntryById($eiUtils->idRepToId($targetIdRep));
		} catch (UnknownEntryException $e) {
			return;
		} catch (\InvalidArgumentException $e) {
			return;
		}

		$nsu = new NestedSetUtils($eiUtils->em(), $eiUtils->getClass());
		
		try {
			if ($before === true) {
				$nsu->moveBefore($liveEntry->getEntityObj(), $targetLiveEntry->getEntityObj());
			} else if ($before === false) {
				$nsu->moveAfter($liveEntry->getEntityObj(), $targetLiveEntry->getEntityObj());
			} else {
				$nsu->move($liveEntry->getEntityObj(), $targetLiveEntry->getEntityObj());
			}
		} catch (\n2n\util\ex\IllegalStateException $e) {
		}
	}
}
