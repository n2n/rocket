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
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use rocket\ei\manage\gui\ViewMode;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use rocket\ei\manage\entry\EiField;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\relation\model\ToOneEiField;
use rocket\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\model\gui\RelationLinkGuiField;
use rocket\impl\ei\component\prop\relation\model\gui\ToOneGuiField;
use rocket\ei\component\prop\FieldEiProp;
use rocket\ei\component\prop\QuickSearchableEiProp;
use rocket\impl\ei\component\prop\relation\model\filter\ToOneQuickSearchProp;
use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\impl\ei\component\prop\adapter\config\QuickSearchConfig;

class OneToOneSelectEiProp extends RelationEiPropAdapter implements FieldEiProp, QuickSearchableEiProp {
	private $quickSearchableConfig;
	
	public function __construct() {
		parent::__construct();
		
		$this->setup(
				new DisplayConfig(ViewMode::all()), 
				new RelationModel($this, false, false, RelationModel::MODE_SELECT, new EditConfig()));
		
		$this->quickSearchableConfig = new QuickSearchConfig();
	}
	
	protected function prepare() {
		parent::prepare();
		$this->getConfigurator()->addAdaption($this->quickSearchableConfig);
	}
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ToOneEntityProperty 
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_ONE);
	
		parent::setEntityProperty($entityProperty);
	}
	
	function buildEiField(Eiu $eiu): ?EiField {
		$targetEiuFrame = $eiu->frame()->forkSelect($this, $eiu->object())
				->frame()->exec($this->getRelationModel()->getTargetReadEiCommandPath());
		
		$field = new ToOneEiField($eiu, $targetEiuFrame, $this, $this->getRelationModel());
		$field->setMandatory($this->getEditConfig()->isMandatory());
		return $field;
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		if ($readOnly || $this->getEditConfig()->isReadOnly()) {
			return new RelationLinkGuiField($eiu, $this->getRelationModel());
		}
		
		return new ToOneGuiField($eiu, $this->getRelationModel());
	}
	
	function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		if (!$this->quickSearchableConfig->isQuickSerachable()) {
			return null;
		}
		
		$targetEiuEngine = $this->getRelationModel()->getTargetEiuEngine();
		$targetDefPropPaths = $targetEiuEngine->idNameDefinition()->getUsedDefProps();
		
		if (empty($targetDefPropPaths)) {
			return null;
		}
		
		return new ToOneQuickSearchProp($this->getRelationModel(), $targetDefPropPaths, 
				$eiu->frame()->forkDiscover($this, $eiu->object()));
	}
}
