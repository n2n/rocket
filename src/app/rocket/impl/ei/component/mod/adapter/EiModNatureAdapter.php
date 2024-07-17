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
namespace rocket\impl\ei\component\mod\adapter;

use rocket\op\ei\component\modificator\EiModNature;
use rocket\impl\ei\component\EiComponentNatureAdapter;
use rocket\ui\gui\EiGuiValueBoundary;
use rocket\op\ei\manage\draft\DraftDefinition;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\component\modificator\EiMod;
use n2n\util\ex\IllegalStateException;

abstract class EiModNatureAdapter extends EiComponentNatureAdapter implements EiModNature {
	private $wrapper;
	
	public function setWrapper(EiMod $wrapper) {
		$this->wrapper = $wrapper;
	}
	
	public function getWrapper(): EiMod {
		if ($this->wrapper !== null) {
			return $this->wrapper;
		}
		
		throw new IllegalStateException(get_class($this) . ' is not assigned to a Wrapper.');
	}
	
	public function getIdBase(): ?string {
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\EiComponentNature::equals()
	 */
	public function equals(mixed $obj): bool {
		return $obj instanceof EiModNature && $this->getWrapper()->getEiModPath()->equals(
				$obj->getWrapper()->getEiModificatorPath());
		
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\modificator\EiModNature::setupEiFrame()
	 */
	public function setupEiFrame(Eiu $eiu) {}
		
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\modificator\EiModNature::setupEiEntry()
	 */
	public function setupEiEntry(Eiu $eiu) {}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\modificator\EiModNature::setupEiGuiDefinition()
	 */
	public function setupEiGuiDefinition(Eiu $eiu) { }
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\modificator\EiModNature::setupEiGuiMaskDeclaration()
	 */
	public function setupEiGuiMaskDeclaration(Eiu $eiu) { }
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\modificator\EiModNature::setupEiGuiValueBoundary()
	 */
	public function setupEiGuiValueBoundary(EiGuiValueBoundary $eiGuiValueBoundary) {}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\modificator\EiModNature::setupDraftDefinition()
	 */
	public function setupDraftDefinition(DraftDefinition $draftDefinition) {}
}