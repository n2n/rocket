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
namespace rocket\op\ei\component\modificator;

use rocket\op\ei\component\EiComponentCollection;
use rocket\op\ei\EiType;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\EiModPath;
use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
use rocket\op\ei\util\Eiu;
use n2n\util\magic\MagicContext;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\critmod\quick\QuickSearchDefinition;
use rocket\op\ei\manage\frame\EiFrame;

class EiModCollection extends EiComponentCollection {


	/**
	 * @param EiMask $eiMask
	 */
	public function __construct(EiMask $eiMask) {
		parent::__construct('EiModificator', EiModNature::class);
		$this->setEiMask($eiMask);
	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @return EiMod
	 */
	public function getByPath(EiPropPath $eiPropPath) {
		return $this->getElementByIdPath($eiPropPath);
	}

	/**
	 * @param string|null $id
	 * @param EiModNature $eiModificatorNature
	 * @return EiMod
	 */
	public function add(?string $id, EiModNature $eiModificatorNature) {
		$eiModificatorPath = new EiModPath($this->makeId($id, $eiModificatorNature));
		$eiModificator = new EiMod($eiModificatorPath, $eiModificatorNature, $this);
		
		$this->addEiComponent($eiModificatorPath,
				new EiMod($eiModificatorPath, $eiModificatorNature, $this));

//		$this->uninitializedEiModificators[] = $eiModificator;

		return $eiModificator;
	}


	function setupEiFrame(EiFrame $eiFrame) {
		if ($this->isEmpty()) {
			return;
		}

		$eiu = new Eiu($eiFrame);
		foreach ($this as $eiMod) {
			$eiMod->getNature()->setupEiFrame($eiu);
		}
	}

	function setupEiGuiMaskDeclaration(EiGuiMaskDeclaration $eiGuiMaskDeclaration) {
		if ($this->isEmpty()) {
			return;
		}
		
		$eiu = new Eiu($eiGuiMaskDeclaration);
		foreach ($this as $eiModificator) {
			$eiModificator->getNature()->setupEiGuiMaskDeclaration($eiu);
		}
	}



}
