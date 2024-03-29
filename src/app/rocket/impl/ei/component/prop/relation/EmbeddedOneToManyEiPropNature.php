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
use rocket\op\ei\manage\gui\ViewMode;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\impl\ei\component\prop\relation\model\ToManyEiField;
use rocket\op\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\model\gui\EmbeddedToManyGuiField;

use n2n\util\type\CastUtils;
use rocket\si\content\impl\meta\SiCrumb;
use rocket\si\content\impl\SiFields;
use rocket\op\ei\util\entry\EiuEntry;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\si\meta\SiStructureType;
use rocket\si\content\SiField;

class EmbeddedOneToManyEiPropNature extends RelationEiPropNatureAdapter {

	public function __construct(ToManyEntityProperty $entityProperty, PropertyAccessProxy $accessProxy) {
		ArgUtils::assertTrue($entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_MANY);

		parent::__construct($entityProperty, $accessProxy,
				new RelationModel($this, false, true, RelationModel::MODE_EMBEDDED));

		$this->displayConfig = (new DisplayConfig(ViewMode::all()))
				->setSiStructureType(SiStructureType::SIMPLE_GROUP)
				->setDefaultDisplayedViewModes(ViewMode::bulky());
	}
	
	function buildEiField(Eiu $eiu): ?EiFieldNature {
		$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->object())->frame()
				->exec($this->getRelationModel()->getTargetReadEiCmdPath());
		
		return new ToManyEiField($eiu, $targetEiuFrame, $this, $this->getRelationModel());
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		$readOnly = $readOnly || $this->getRelationModel()->isReadOnly();
		
		if ($readOnly && $eiu->guiMaskDeclaration()->isCompact()) {
			return $this->createCompactGuiField($eiu);
		}
		
		$targetEiuFrame = null; 
		if ($readOnly){
			$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->object())->frame()
					->exec($this->getRelationModel()->getTargetReadEiCmdPath());
		} else {
			$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->object())->frame()
					->exec($this->getRelationModel()->getTargetReadEiCmdPath());
		}
		
		return new EmbeddedToManyGuiField($eiu, $targetEiuFrame, $this->getRelationModel(), $readOnly);
	}

	private function createCompactGuiField(Eiu $eiu): GuiField {
		$siCrumbs = [];
		foreach ($eiu->field()->getValue() as $eiuEntry) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$siCrumbs[] = SiCrumb::createIcon($eiuEntry->mask()->getIconType())
					->setTitle($eiuEntry->createIdentityString())
					->setSeverity(SiCrumb::SEVERITY_IMPORTANT);
		}
		
		return $eiu->factory()->newGuiField(SiFields::crumbOut(...$siCrumbs)
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs()))->toGuiField();
	}
}