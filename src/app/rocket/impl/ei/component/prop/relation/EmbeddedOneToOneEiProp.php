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
namespace rocket\impl\ei\component\prop\relation;

use n2n\util\type\ArgUtils;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\ei\manage\gui\ViewMode;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\si\structure\SiStructureType;

class EmbeddedOneToOneEiProp extends RelationEiPropAdapter {
	
	/**
	 * 
	 */
	public function __construct() {
		parent::__construct();
		
		$this->configurator->registerDisplayConfig((new DisplayConfig(ViewMode::bulky()))
				->setDisplayItemType(SiStructureType::SIMPLE_GROUP));
		
		$this->configurator->setRelationModel(
				new RelationModel($this, false, false, RelationModel::MODE_EMBEDDED, $this->editConfig));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\relation\ToOneEiPropAdapter::setEntityProperty()
	 */
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ToOneEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_ONE);
	
		parent::setEntityProperty($entityProperty);
	}
}