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

use n2n\persistence\orm\property\EntityProperty;
use n2n\util\type\ArgUtils;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\impl\persistence\orm\property\ToManyEntityProperty;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\util\Eiu;
use rocket\ei\manage\entry\EiField;
use rocket\impl\ei\component\prop\relation\model\ToManyEiField;
use rocket\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\model\gui\EmbeddedToManyGuiField;
use rocket\impl\ei\component\prop\relation\model\gui\RelationLinkGuiField;
use rocket\ei\component\prop\FieldEiProp;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;

class EmbeddedOneToManyEiProp extends RelationEiPropAdapter implements FieldEiProp {

	/**
	 * 
	 */
	public function __construct() {	
		parent::__construct();
		
		$this->setup(
				new DisplayConfig(ViewMode::all()),
				new RelationModel($this, false, true, RelationModel::MODE_EMBEDDED, new EditConfig()));
	}
	
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyEiPropAdapter::setEntityProperty()
	 */
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ToManyEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_MANY);
	
		parent::setEntityProperty($entityProperty);
	}
	
	function buildEiField(Eiu $eiu): ?EiField {
		$targetEiuFrame = $eiu->frame()->forkDiscover($this, $eiu->object())
				->exec($this->getRelationModel()->getTargetReadEiCommandPath());
		
		return new ToManyEiField($eiu, $targetEiuFrame, $this, $this->getRelationModel());
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		if ($readOnly || $this->editConfig->isReadOnly()) {
			return new RelationLinkGuiField($eiu, $this->getRelationModel());
		}
		
		$targetEiuFrame = $eiu->frame()->forkDiscover($this, $eiu->object())
				->exec($this->getRelationModel()->getTargetEditEiCommandPath());
		
		return new EmbeddedToManyGuiField($eiu, $targetEiuFrame, $this->getRelationModel());
	}
}