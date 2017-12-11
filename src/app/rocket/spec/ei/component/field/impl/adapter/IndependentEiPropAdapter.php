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
namespace rocket\spec\ei\component\field\impl\adapter;

use rocket\spec\ei\component\impl\IndependentEiComponentAdapter;
use rocket\spec\ei\component\field\indepenent\IndependentEiProp;
use rocket\spec\ei\component\field\EiProp;
use rocket\spec\ei\component\field\indepenent\EiPropConfigurator;
use n2n\l10n\Lstr;
use n2n\util\StringUtils;
use rocket\spec\ei\component\EiConfigurator;

abstract class IndependentEiPropAdapter extends IndependentEiComponentAdapter implements IndependentEiProp {
	protected $parentEiProp;
	protected $labelLstr;
		
	public final function createEiConfigurator(): EiConfigurator {
		return $this->createEiPropConfigurator();
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		return new AdaptableEiPropConfigurator($this);
	}
	
	public function getParentEiProp() {
		return $this->parentEiProp;
	}
	
	public function setParentEiProp(EiProp $parentEiProp = null) {
		return $this->parentEiProp = $parentEiProp;
	}
	
	public function getLabelLstr(): Lstr {
		if ($this->labelLstr === null) {
			$this->labelLstr = new Lstr(StringUtils::pretty($this->getId()));
		}
		
		return $this->labelLstr;
	}

	public function setLabelLstr(Lstr $labelLstr) {
		$this->labelLstr = $labelLstr;
	}
	
	public function equals($obj) {
		return $obj instanceof EiProp && parent::equals($obj);
	}
}
