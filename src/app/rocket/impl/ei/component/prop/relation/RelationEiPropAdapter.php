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
namespace rocket\impl\ei\component\prop\relation;

use rocket\impl\ei\component\prop\relation\model\relation\EiPropRelation;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\component\prop\FieldEiProp;
use rocket\ei\manage\entry\EiField;
use rocket\impl\ei\component\prop\adapter\entry\Readable;
use rocket\impl\ei\component\prop\adapter\entry\Writable;
use rocket\ei\manage\EiObject;
use rocket\impl\ei\component\prop\relation\conf\RelationEiPropConfigurator;
use n2n\core\container\N2nContext;
use rocket\ei\manage\security\filter\SecurityFilterProp;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\impl\ei\component\prop\adapter\PropertyEiPropAdapter;
use rocket\impl\ei\component\prop\adapter\entry\Copyable;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;

abstract class RelationEiPropAdapter extends PropertyEiPropAdapter implements RelationEiProp, GuiEiProp, 
		FieldEiProp, Readable, Writable, Copyable {
	/**
	 * @var EiPropRelation
	 */
	protected $eiPropRelation;
	protected $draftable = false;
	protected $editConfig;
	
	protected function initialize(EiPropRelation $eiPropRelation, EditConfig $editConfig = null) {
		$this->eiPropRelation = $eiPropRelation;
		
		if ($editConfig !== null) {
			$this->editConfig = $editConfig;
		} else {
			$this->editConfig = new EditConfig();
		}
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
	
	public function getEditConfig(): EditConfig {
		return $this->editConfig;
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
	
	public function createSecurityFilterProp(N2nContext $n2nContext): SecurityFilterProp {
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
