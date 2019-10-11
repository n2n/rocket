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
namespace rocket\core\model;

use n2n\context\Lookupable;
use n2n\core\container\N2nContext;
use n2n\validation\impl\ValidationMessages;

class AnglTemplateModel implements Lookupable {
	private $n2nContext;
	
	private function _init(N2nContext $n2nContext) {
		$this->n2nContext = $n2nContext;
	}
	
	function getData() {
		return [
			'translationMap' => $this->createTranslationMap()	
		];
	}
	
	function createTranslationMap() {
		$n2nLocale = $this->n2nContext->getN2nLocale();
		
		return [
			'mandatory_err' => ValidationMessages::mandatory('{field}')->t($n2nLocale),
			'minlength_err' => ValidationMessages::minlength('{minlength}', '{field}')->t($n2nLocale),
			'maxlength_err' => ValidationMessages::maxlength('{maxlength}', '{field}')->t($n2nLocale)
		];
	}
}
