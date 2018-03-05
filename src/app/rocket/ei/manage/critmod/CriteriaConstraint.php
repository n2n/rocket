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
namespace rocket\ei\manage\critmod;

use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\criteria\item\CriteriaProperty;

interface CriteriaConstraint {
	const TYPE_MANAGE = 1;
	const TYPE_TMP_FILTER = 2;
	const TYPE_TMP_SORT = 4;
	const TYPE_SECURITY = 8;
	const TYPE_HARD_FILTER = 16;
	const TYPE_HARD_SORT = 32;
	
	const TMP_TYPES = 6;
	const HARD_TYPES = 48;
	const ALL_TYPES = 63;
	const NON_SECURITY_TYPES = 55;
	
	/**
	 * @param Criteria $criteria
	 * @param CriteriaProperty $alias
	 */
	public function applyToCriteria(Criteria $criteria, CriteriaProperty $alias);
}
