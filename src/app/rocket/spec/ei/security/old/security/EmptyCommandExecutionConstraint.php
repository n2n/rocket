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
namespace rocket\spec\ei\security;

use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\security\CommandExecutionConstraint;
use rocket\spec\ei\manage\mapping\EiEntry;
use n2n\persistence\orm\criteria\Criteria;
use n2n\util\ex\NotYetImplementedException;
use n2n\persistence\orm\criteria\item\CriteriaProperty;

class EmptyCommandExecutionConstraint implements CommandExecutionConstraint {
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiEntryConstraint::acceptValues()
	 */
	public function acceptValues(\ArrayAccess $values) {
		return true;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiEntryConstraint::acceptValue()
	 */
	public function acceptValue($id, $value) {
		return true;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\MappingValidator::validate()
	 */
	public function validate(EiEntry $eiEntry) {
		return true;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\critmod\CriteriaConstraint::applyToCriteria()
	 */
	public function applyToCriteria(Criteria $criteria, CriteriaProperty $alias) {	
	}
	
	public function acceptsValue(EiPropPath $eiPropPath, $value): bool {
	}

	public function check(EiEntry $eiEntry): bool {
		throw new NotYetImplementedException();
	}


}
