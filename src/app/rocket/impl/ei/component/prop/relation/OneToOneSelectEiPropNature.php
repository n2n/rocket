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

use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\util\type\ArgUtils;
use n2n\persistence\orm\property\EntityProperty;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\adapter\config\EditAdapter;
use rocket\ei\manage\gui\ViewMode;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use rocket\ei\manage\entry\EiField;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\relation\model\ToOneEiField;
use rocket\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\model\gui\RelationLinkGuiField;
use rocket\impl\ei\component\prop\relation\model\gui\ToOneGuiField;


use rocket\impl\ei\component\prop\relation\model\filter\ToOneQuickSearchProp;
use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\impl\ei\component\prop\adapter\QuickSearchTrait;
use rocket\impl\ei\component\prop\adapter\EditableAdapter;

class OneToOneSelectEiPropNature extends RelationEiPropNatureAdapter {
	use QuickSearchTrait;
	
	public function __construct() {
		$this->relationModel = new RelationModel($this, false, false, RelationModel::MODE_SELECT);
		
		
	}

//	public function setEntityProperty(?EntityProperty $entityProperty) {
//		ArgUtils::assertTrue($entityProperty instanceof ToOneEntityProperty
//				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_ONE);
//
//		parent::setEntityProperty($entityProperty);
//	}
	
	function buildEiField(Eiu $eiu): ?EiField {
		$targetEiuFrame = $eiu->frame()->forkSelect($eiu->prop(), $eiu->object())
				->frame()->exec($this->getRelationModel()->getTargetReadEiCmdPath());
		
		$field = new ToOneEiField($eiu, $targetEiuFrame, $this, $this->getRelationModel());
		$field->setMandatory($this->relationModel->isMandatory());
		return $field;
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		if ($readOnly || $this->relationModel->isReadOnly()) {
			return new RelationLinkGuiField($eiu, $this->getRelationModel());
		}
		
		return new ToOneGuiField($eiu, $this->getRelationModel());
	}
	
	function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		if (!$this->isQuickSerachable()) {
			return null;
		}
		
		$targetEiuEngine = $this->getRelationModel()->getTargetEiuEngine();
		$targetDefPropPaths = $targetEiuEngine->getIdNameDefinition()->getAllIdNameProps();
		
		if (empty($targetDefPropPaths)) {
			return null;
		}
		
		return new ToOneQuickSearchProp($this->getRelationModel(), $targetDefPropPaths, 
				$eiu->frame()->forkDiscover($eiu->prop()));
	}
}
