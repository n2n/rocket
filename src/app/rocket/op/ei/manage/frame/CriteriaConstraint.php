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
namespace rocket\op\ei\manage\frame;

use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\criteria\item\CriteriaProperty;

/**
 * <p>Can make generic modifications to {@see Criteria}s that are passed to {@see self::applyToCriteria()}. 
 * Among other things implementations of this Interface are usued to make modification to {@see Criteria}s created by 
 * an {@see \rocket\op\ei\manage\frame\EiFrame}.</p>
 *  
 * @see \rocket\op\ei\manage\frame\EiFrame::getBoundry()
 *
 */
interface CriteriaConstraint {
	
	/**
	 * <p>The modifications represented by CriteriaConstraint object are always relative to an Entity. Usually the 
	 * entity you wish to select. The select alias has to be passed as second argument.</p>
	 * 
	 * @param Criteria $criteria 
	 * @param CriteriaProperty $alias Alias of the main Entity. 
	 */
	public function applyToCriteria(Criteria $criteria, CriteriaProperty $alias);
}