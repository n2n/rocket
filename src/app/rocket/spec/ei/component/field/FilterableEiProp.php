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
namespace rocket\spec\ei\component\field;

use rocket\spec\ei\manage\EiFrame;
use n2n\core\container\N2nContext;

interface FilterableEiProp extends EiProp {
	
	/**
	 * @param EiFrame $eiFrame
	 * @return \rocket\spec\ei\manage\critmod\FilterField|null
	 */
	public function buildManagedFilterField(EiFrame $eiFrame);
	
	/**
	 * @param N2nContext $n2nContext
	 * @return \rocket\spec\ei\manage\critmod\FilterField|null
	 */
	public function buildFilterField(N2nContext $n2nContext);
	
	/**
	 * @param N2nContext $n2nContext
	 * @return \rocket\spec\ei\manage\critmod\filter\EiEntryFilterField|null
	 */
	public function buildEiEntryFilterField(N2nContext $n2nContext);
	
}
