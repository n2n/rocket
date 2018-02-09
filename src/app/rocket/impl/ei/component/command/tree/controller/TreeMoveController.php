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
namespace rocket\impl\ei\component\command\tree\controller;

use rocket\spec\ei\manage\ManageState;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\ParamGet;
use rocket\spec\ei\manage\util\model\UnknownEntryException;
use n2n\persistence\orm\util\NestedSetUtils;
use rocket\spec\ei\manage\util\model\EiuCtrl;

class TreeMoveController extends ControllerAdapter {
	private $eiCtrl;

	public function prepare(ManageState $manageState, EiuCtrl $eiuCtrl) {
		$this->eiCtrl = $eiuCtrl;
	}

	public function doChild($targetEiId, ParamGet $eiIds, ParamGet $refPath) {
		$refUrl = $this->eiCtrl->parseRefUrl($refPath);
		
		foreach ($eiIds->toStringArrayOrReject() as $eiId) {
			$this->move($eiId, $targetEiId);
		}
		
		$this->eiCtrl->redirectToReferer($refUrl);
	}
	
	public function doBefore($targetEiId, ParamGet $eiIds, ParamGet $refPath) {
		$refUrl = $this->eiCtrl->parseRefUrl($refPath);

		foreach ($eiIds->toStringArrayOrReject() as $eiId) {
			$this->move($eiId, $targetEiId, true);
		}
		
		$this->eiCtrl->redirectToReferer($refUrl);
	}

	public function doAfter($targetEiId, ParamGet $eiIds, ParamGet $refPath) {
		$refUrl = $this->eiCtrl->parseRefUrl($refPath);

		foreach (array_reverse($eiIds->toStringArrayOrReject()) as $eiId) {
			$this->move($eiId, $targetEiId, false);
		}

		$this->eiCtrl->redirectToReferer($refUrl);
	}

	private function move(string $eiId, string $targetEiId, bool $before = null) {
		if ($eiId === $targetEiId) return;

		$eiUtils = $this->eiCtrl->frame();
		
		$nestedSetStrategy = $eiUtils->getNestedSetStrategy();
		if ($nestedSetStrategy === null) return;
		
		$eiEntityObj = null;
		$targetEiEntityObj = null;

		try {
			$eiEntityObj = $eiUtils->lookupEiEntityObj($eiUtils->eiIdToId($eiId));
			$targetEiEntityObj = $eiUtils->lookupEiEntityObj($eiUtils->eiIdToId($targetEiId));
		} catch (UnknownEntryException $e) {
			return;
		} catch (\InvalidArgumentException $e) {
			return;
		}

		$nsu = new NestedSetUtils($eiUtils->em(), $eiUtils->getClass());
		
		try {
			if ($before === true) {
				$nsu->moveBefore($eiEntityObj->getEntityObj(), $targetEiEntityObj->getEntityObj());
			} else if ($before === false) {
				$nsu->moveAfter($eiEntityObj->getEntityObj(), $targetEiEntityObj->getEntityObj());
			} else {
				$nsu->move($eiEntityObj->getEntityObj(), $targetEiEntityObj->getEntityObj());
			}
		} catch (\n2n\util\ex\IllegalStateException $e) {
		}
	}
}
