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
namespace rocket\spec\ei\component\field\impl\relation\conf;

use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\spec\ei\component\EiSetupProcess;
use rocket\spec\ei\component\field\impl\relation\model\relation\SelectEiFieldRelation;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\config\LenientAttributeReader;

class SelectEiFieldConfigurator extends RelationEiFieldConfigurator {
	const OPTION_FILTERED_KEY = 'filtered';
	const OPTION_EMBEDDED_ADD_KEY = 'embeddedAddEnabled';

	private $selectEiFieldRelation;
	
	public function __construct(SelectEiFieldRelation $selectEiFieldRelation) {
		parent::__construct($selectEiFieldRelation->getRelationEiField());
		$this->selectEiFieldRelation = $selectEiFieldRelation;
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$lar = new LenientAttributeReader($this->attributes);
		
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection(); 
		$magCollection->addMag(new BoolMag(self::OPTION_FILTERED_KEY, 'Filtered',
				$lar->getBool(self::OPTION_FILTERED_KEY, $this->selectEiFieldRelation->isFiltered())));
		$magCollection->addMag(new BoolMag(self::OPTION_EMBEDDED_ADD_KEY,
				'Embedded Add Enabled', $lar->getBool(self::OPTION_EMBEDDED_ADD_KEY, 
						$this->selectEiFieldRelation->isEmbeddedAddEnabled())));
		return $magDispatchable;
	}
	
	public function setup(EiSetupProcess $eiSetupProcess) {
		if ($this->attributes->contains(self::OPTION_FILTERED_KEY)) {
			$this->selectEiFieldRelation->setFiltered($this->attributes->getBool(self::OPTION_FILTERED_KEY));
		}
		
		if ($this->attributes->contains(self::OPTION_EMBEDDED_ADD_KEY)) {
			$this->selectEiFieldRelation->setEmbeddedAddEnabled($this->attributes->get(self::OPTION_EMBEDDED_ADD_KEY));
		}
		
		if ($this->selectEiFieldRelation->isEmbeddedAddEnabled() && !$this->selectEiFieldRelation->isPersistCascaded()) {
			throw $eiSetupProcess->createException('Option ' . self::OPTION_EMBEDDED_ADD_KEY 
					. ' requires an EntityProperty which cascades persist.');
		}
		
		parent::setup($eiSetupProcess);
	}
}
