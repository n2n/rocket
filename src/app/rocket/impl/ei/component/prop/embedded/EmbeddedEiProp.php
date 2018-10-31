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
namespace rocket\impl\ei\component\prop\date;

use rocket\impl\ei\component\prop\adapter\ObjectPropertyEiPropAdapter;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\EmbeddedEntityProperty;
use n2n\reflection\ArgUtils;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\FieldEiProp;
use rocket\ei\component\prop\FieldForkEiProp;
use rocket\ei\manage\entry\EiFieldFork;
use n2n\reflection\CastUtils;
use n2n\reflection\ReflectionUtils;
use rocket\ei\component\prop\field\EiFieldAdapter;

class EmbeddedEiProp extends ObjectPropertyEiPropAdapter implements GuiEiProp, FieldEiProp, FieldForkEiProp {
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof EmbeddedEntityProperty);
		
		parent::setEntityProperty($entityProperty);
	}
	
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		
	}
	
	public function buildEiField(Eiu $eiu): ?EiField {
		$eiFieldMap = $this->buildEiFieldMap($eiu, false);
		
	}
	
}

class EmbeddedEiField extends EiFieldAdapter implements EiFieldFork {
	private $eiu;
	private $eiProp;
	
	private $forkedEiFieldMap;
	
	public function __construct(Eiu $eiu, EmbeddedEiProp $eiProp) {
		$this->eiu = $eiu;	
		$this->eiProp = $eiProp;
	}
	
	private function buildEiFieldMap(bool $createTargetLiveObject) {
		$entityProperty = $this->getEntityProperty(true);
		CastUtils::assertTrue($entityProperty instanceof EmbeddedEntityProperty);
		
		$liveObject = $eiu->fieldMap()->getLiveObject();
		
		$targetLiveObject = $this->eiProp->getObjectPropertyAccessProxy(true)->getValue($liveObject);
		if ($targetLiveObject === null) {
			$targetLiveObject = ReflectionUtils::createObject($this->eiProp->getEntityProperty(true)
					->getEmbeddedEntityPropertyCollection()->getClass());
		}
		
		return $eiu->engine()->createEiFieldMap($eiu->mask()->forkedProps($this), $targetLiveObject);
	}
	
	protected function readValue() {
		$this->forkedEiFieldMap = $this->buildEiFieldMap(true);
	}

	protected function validateValue($value) {
		
	}

	public function isWritable(): bool {
		return true;
	}

	public function copyEiField(Eiu $copyEiu) {
		return null;
	}

	protected function writeValue($value) {
	}

	public function isReadable(): bool {
	}
	
	public function hasForkedEiFieldMap(): bool {
		return true;
	}
	
	public function getForkedEiFieldMap(): EiFieldMap {
		
	}
}

class EmbeddedEiFieldFork implements EiFieldFork {
	private $eiFieldMap;
	
	public function __construct(EiFieldMap $forkedEiFieldMap) {
		$this->eiFieldMap = $forkedEiFieldMap;
	}
	
	public function getForkedEiFieldMap(): EiFieldMap {
		return $this->eiFieldMap;
	}
	
	public function write() {
		
	}
}