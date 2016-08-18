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
namespace rocket\spec\security\impl;

use rocket\spec\security\EiCommandPrivilege;
use n2n\l10n\N2nLocale;
use n2n\l10n\Lstr;

class CommonEiCommandPrivilege implements EiCommandPrivilege {
	private $labelLstr;
	private $subEiCommandPrivileges = array();
	
	public function __construct(Lstr $labelLstr) {
		$this->labelLstr = $labelLstr;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\security\EiCommandPrivilege::getLabel($n2nLocale)
	 */
	public function getLabel(N2nLocale $n2nLocale): string {
		return $this->labelLstr->t($n2nLocale);
	}

	public function putSubEiCommandPrivilege(string $key, EiCommandPrivilege $eiCommandPrivilege) {
		$this->subEiCommandPrivileges[$key] = $eiCommandPrivilege;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\security\EiCommandPrivilege::getSubEiCommandPrivileges()
	 */
	public function getSubEiCommandPrivileges(): array {
		return $this->subEiCommandPrivileges;
	}
}
