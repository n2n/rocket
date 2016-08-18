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
namespace rocket\spec\ei\component\modificator\impl\cache;

use rocket\spec\ei\component\modificator\impl\IndependentEiModificatorAdapter;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\mapping\OnWriteMappingListener;
use n2n\util\config\Attributes;
use rocket\spec\ei\listener\impl\cache\ClearCacheIndicator;
use n2n\N2N;

class ClearCacheModificator extends IndependentEiModificatorAdapter {
	
	private $clearChacheIndicator;
	
	private function _init(ClearCacheIndicator $clearChacheIndicator) {
		$this->clearChacheIndicator = $clearChacheIndicator;
	}
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		N2N::getN2nContext()->magicInit($this);
	}
	
	public function setupEiMapping(EiState $eiState, EiMapping $eiMapping) {
		
		$that = $this;
		
		$eiMapping->registerListener(new OnWriteMappingListener(function() use ($that) {
			$that->clearChacheIndicator->clearCache();
		}));
	}
	
}
