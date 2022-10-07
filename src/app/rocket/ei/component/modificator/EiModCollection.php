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
namespace rocket\ei\component\modificator;

use rocket\ei\component\EiComponentCollection;
use rocket\ei\EiType;
use rocket\ei\mask\EiMask;
use rocket\ei\EiModificatorPath;
use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\util\Eiu;
use n2n\util\magic\MagicContext;

class EiModCollection extends EiComponentCollection {


	/**
	 * @param EiMask $eiMask
	 */
	public function __construct(EiMask $eiMask) {
		parent::__construct('EiModificator', EiModNature::class);
		$this->setEiMask($eiMask);
	}

	public function getById(string $id): EiMod {
		return $this->getEiComponentById($id);
	}

	/**
	 * @param EiModNature $eiModificatorNature
	 * @param string|null $id
	 * @param bool $prepend
	 * @return EiMod
	 */
	public function add(EiModNature $eiModificatorNature, string $id = null, bool $prepend = false) {
		$eiModificatorPath = new EiModificatorPath($this->makeId($id, $eiModificatorNature));
		$eiModificator = new EiMod($eiModificatorPath, $eiModificator, $this);
		
		$this->addEiComponent($eiModificatorPath,
				new EiMod($eiModificatorPath, $eiModificatorNature, $this));

		$this->uninitializedEiModificators[] = $eiModificator;

		return $eiModificator;
	}

	function setupEiGuiFrame(EiGuiFrame $eiGuiFrame) {
		if ($this->isEmpty()) {
			return;
		}
		
		$eiu = new Eiu($eiGuiFrame);
		foreach ($this as $eiModificator) {
			$eiModificator->setupEiGuiFrame($eiu);
		}
	}
	
}
