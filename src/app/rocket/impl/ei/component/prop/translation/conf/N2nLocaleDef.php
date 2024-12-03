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
namespace rocket\impl\ei\component\prop\translation\conf;

use n2n\l10n\N2nLocale;

class N2nLocaleDef {
	private $n2nLocale;
	private $mandatory;
	private $label;
	
	public function __construct(N2nLocale $n2nLocale, bool $mandatory, ?string $label = null) {
		$this->n2nLocale = $n2nLocale;
		$this->mandatory = $mandatory;
		$this->label = $label;
	}
	
	public function getN2nLocaleId() {
		return $this->n2nLocale->getId();
	}
	
	public function getN2nLocale() {
		return $this->n2nLocale;
	}
	
	public function isMandatory(): bool {
		return $this->mandatory;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function buildLabel(N2nLocale $n2nLocale) {
		if ($this->label !== null) {
			return $this->label;
		}
		
		return $this->n2nLocale->getName($n2nLocale);
	}
}
