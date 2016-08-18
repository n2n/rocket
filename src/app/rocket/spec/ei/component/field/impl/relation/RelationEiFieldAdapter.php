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

use rocket\spec\ei\component\field\impl\relation\model\relation\EiFieldRelation;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\field\GuiEiField;
use rocket\spec\ei\component\field\MappableEiField;
use rocket\spec\ei\manage\mapping\Mappable;
use rocket\spec\ei\manage\mapping\impl\Readable;
use rocket\spec\ei\manage\mapping\impl\Writable;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\component\field\impl\relation\conf\RelationEiFieldConfigurator;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\critmod\filter\EiMappingFilterField;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use rocket\spec\ei\component\field\impl\adapter\ConfDraftableEiField;
use rocket\spec\ei\component\field\impl\adapter\ConfObjectPropertyEiFieldAdapter;

abstract class RelationEiFieldAdapter extends ConfObjectPropertyEiFieldAdapter implements RelationEiField, GuiEiField, 
		MappableEiField, Readable, Writable, ConfDraftableEiField {
	/**
	 * @var EiFieldRelation
	 */
	protected $eiFieldRelation;
	protected $draftable = false;
	
	protected function initialize(EiFieldRelation $eiFieldRelation) {
		$this->eiFieldRelation = $eiFieldRelation;
	}
	
	public function getEiFieldRelation(): EiFieldRelation {
		if ($this->eiFieldRelation !== null) {
			return $this->eiFieldRelation;
		}
		
		throw new IllegalStateException();
	}

	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new RelationEiFieldConfigurator($this);
	}
	
	public function isMappable(): bool {
		return true;
	}
	
	public function buildMappableFork(EiObject $eiObject, Mappable $mappable = null) {
		return null;
	}
	
	public function isEiMappingFilterable(): bool {
		return false;
	}
	
	public function createEiMappingFilterField(N2nContext $n2nContext): EiMappingFilterField {
		throw new IllegalStateException();	
	}
	
	public function setDraftable(bool $draftable) {
		$this->draftable = $draftable;
	}
	
	public function isDraftable(): bool {
		return $this->draftable;
	}

	public function getDraftProperty() {
		return $this;
	}
}
