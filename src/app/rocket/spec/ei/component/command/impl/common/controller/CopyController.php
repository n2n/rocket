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
namespace rocket\spec\ei\component\command\impl\common\controller;

use n2n\l10n\DynamicTextCollection;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\manage\ManageState;
use n2n\web\http\controller\ControllerAdapter;
use n2n\reflection\ReflectionContext;
use rocket\spec\ei\EntityChangeEvent;
use rocket\spec\ei\manage\gui\Editable;
use n2n\web\http\NoHttpRefererGivenException;
use rocket\spec\ei\component\field\ObjectPropertyEiProp;

class CopyController extends ControllerAdapter {
	/**
	 * @var \rocket\spec\ei\
	 */
	private $eiSpec;
	private $dtc;
	private $utils;
	
	private function _init(DynamicTextCollection $dtc, ManageState $manageState) {
		$this->dtc = $dtc;
		$this->utils = new EntryControllingUtils($this->eiSpec, $manageState);
	}
	
	public function setEiSpec(EiSpec $eiSpec) {
		$this->eiSpec = $eiSpec;
	}
	
	public function index($id) {
		$eiFrame = $this->utils->getEiFrame();
		$eiObject = $eiFrame->getEiObject();

		$em = $eiFrame->getEntityManager();;
		$currentObject = $em->find($this->eiSpec->getEntityModel()->getClass(), $id);
		$newObject = ReflectionContext::createObject($this->eiSpec->getEntityModel()->getClass());
		foreach ($this->eiSpec->getEiPropCollection()->toArray() as $eiProp) {
			if (!($eiProp instanceof Editable) || $eiProp->isReadOnly() || !($eiProp instanceof ObjectPropertyEiProp)) continue;
			$accessProxy = $eiProp->getObjectPropertyAccessProxy();
			$accessProxy->setValue($newObject, $eiProp->getEntityProperty()->copy($accessProxy->getValue($currentObject)));
		}
		$eiFrame->triggerOnNewObject($em, $newObject);
		
		$this->eiSpec->notifyObjectMod(EntityChangeEvent::TYPE_ON_INSERT, $newObject);
		$em->persist($newObject);
		$this->eiSpec->notifyObjectMod(EntityChangeEvent::TYPE_INSERTED, $newObject);
		
		try {
			$this->redirectToReferer();
		} catch (NoHttpRefererGivenException $e) {
			$this->redirectToController($this->eiSpec->getEntryDetailPathExt($eiObject->toEntryNavPoint()),
					null, null, $eiFrame->getControllerContext());
			return;
		}
	}
}
