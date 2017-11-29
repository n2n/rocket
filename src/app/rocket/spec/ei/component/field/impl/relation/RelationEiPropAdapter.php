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
namespace rocket\spec\ei\component\field\impl\relation;

use rocket\spec\ei\component\field\impl\relation\model\relation\EiPropRelation;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\field\GuiEiProp;
use rocket\spec\ei\component\field\FieldEiProp;
use rocket\spec\ei\manage\mapping\EiField;
use rocket\spec\ei\manage\mapping\impl\Readable;
use rocket\spec\ei\manage\mapping\impl\Writable;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\component\field\impl\relation\conf\RelationEiPropConfigurator;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\critmod\filter\EiEntryFilterField;
use rocket\spec\ei\component\field\indepenent\EiPropConfigurator;
use rocket\spec\ei\component\field\impl\adapter\ObjectPropertyEiPropAdapter;
use rocket\spec\ei\manage\mapping\impl\Copyable;

abstract class RelationEiPropAdapter extends ObjectPropertyEiPropAdapter implements RelationEiProp, GuiEiProp, 
		FieldEiProp, Readable, Writable, Copyable {
	/**
	 * @var EiPropRelation
	 */
	protected $eiPropRelation;
	protected $draftable = false;
	
	protected function initialize(EiPropRelation $eiPropRelation) {
		$this->eiPropRelation = $eiPropRelation;
	}
	
	public function getEiPropRelation(): EiPropRelation {
		if ($this->eiPropRelation !== null) {
			return $this->eiPropRelation;
		}
		
		throw new IllegalStateException();
	}

	public function createEiPropConfigurator(): EiPropConfigurator {
		return new RelationEiPropConfigurator($this);
	}
	
	public function isEiField(): bool {
		return true;
	}
	
	public function buildEiFieldFork(EiObject $eiObject, EiField $eiField = null) {
		return null;
	}
	
	public function isEiEntryFilterable(): bool {
		return false;
	}
	
	public function createEiEntryFilterField(N2nContext $n2nContext): EiEntryFilterField {
		throw new IllegalStateException();	
	}
	
	public function setDraftable(bool $draftable) {
		$this->draftable = $draftable;
	}
	
	public function isDraftable(): bool {
		return $this->draftable;
	}

	public function getDraftProperty() {
		if ($this->isDraftable()) return $this;
		
		return null;
	}
}
