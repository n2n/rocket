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
namespace rocket\impl\ei\component\prop\embedded;

use rocket\impl\ei\component\prop\adapter\ObjectPropertyEiPropAdapter;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\EmbeddedEntityProperty;
use n2n\reflection\ArgUtils;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\FieldEiProp;
use n2n\reflection\CastUtils;
use n2n\reflection\ReflectionUtils;
use rocket\ei\component\prop\field\EiFieldAdapter;
use rocket\ei\manage\entry\EiFieldMap;
use n2n\util\ex\NotYetImplementedException;

class EmbeddedEiProp extends ObjectPropertyEiPropAdapter implements GuiEiProp, FieldEiProp {
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof EmbeddedEntityProperty);
		
		parent::setEntityProperty($entityProperty);
	}
	
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		return null;
	}
	
	public function buildEiField(Eiu $eiu): ?EiField {
		return new EmbeddedEiField($eiu, $this);
	}
	
}

class EmbeddedEiField extends EiFieldAdapter {
	private $eiu;
	private $eiProp;
	
	private $forkedEiFieldMap;
	
	public function __construct(Eiu $eiu, EmbeddedEiProp $eiProp) {
		$this->eiu = $eiu;	
		$this->eiProp = $eiProp;
		
	}
	
	private function buildEiFieldMap($targetObject) {
		$entityProperty = $this->eiProp->getEntityProperty(true);
		CastUtils::assertTrue($entityProperty instanceof EmbeddedEntityProperty);
		
		if ($targetObject === null) {
			$targetObject = ReflectionUtils::createObject($this->eiProp->getEntityProperty(true)
					->getEmbeddedEntityPropertyCollection()->getClass());
		}
		
		return $this->eiu->entry()->newFieldMap($this->eiProp, $targetObject);
	}
	
	protected function readValue() {
		$targetLiveObject = null;
		
		if ($this->eiu->entry()->isDraft()) { 
// 			$targetLiveObject = $this->eiu->entry()->getDraft()->getDraftValueMap()->getValue(EiPropPath::from($this->eiProp));
			throw new NotYetImplementedException();
		} else {
			$targetLiveObject = $this->eiProp->getObjectPropertyAccessProxy(true)->getValue($this->eiu->fieldMap()->getObject());
		}
		
		if ($targetLiveObject !== null) {
			return $this->forkedEiFieldMap = $this->buildEiFieldMap($targetLiveObject);
		}
		
		$this->forkedEiFieldMap = $this->buildEiFieldMap(null);
		return null;
	}

	protected function validateValue($value) {
		throw new NotYetImplementedException();
	}

	public function isWritable(): bool {
		return true;
	}

	public function copyEiField(Eiu $copyEiu) {
		return null;
	}

	protected function writeValue($value) {
		if ($value !== null) {
			CastUtils::assertTrue($value instanceof EiFieldMap);
			$value->write();
			$value = $value->getLiveObject();
		}
		
		if ($this->eiu->entry()->isDraft()) {
			throw new NotYetImplementedException();
		} else {
			$this->eiProp->getObjectPropertyAccessProxy(true)
					->setValue($this->eiu->fieldMap()->getLiveObject(), $value);
		}
	}

	public function isReadable(): bool {
		return true;
	}
	
	public function hasForkedEiFieldMap(): bool {
		return true;
	}
	
	public function getForkedEiFieldMap(): EiFieldMap {
		$this->getValue();
		return $this->forkedEiFieldMap->getEiFieldMap();
	}
}