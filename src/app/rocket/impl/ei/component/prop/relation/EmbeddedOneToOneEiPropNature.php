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
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\ui\gui\ViewMode;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ui\si\meta\SiStructureType;
use rocket\impl\ei\component\prop\relation\model\ToOneEiField;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\ui\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\model\gui\SiEmbeddedToOneGuiField;
use n2n\util\type\CastUtils;
use rocket\ui\si\content\impl\meta\SiCrumb;
use rocket\ui\si\content\impl\SiFields;
use rocket\op\ei\util\entry\EiuEntry;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\ui\gui\field\impl\GuiFields;
use rocket\impl\ei\component\prop\relation\model\gui\RelationGuiEmbeddedEntryFactory;
use n2n\bind\mapper\impl\Mappers;
use n2n\util\col\ArrayUtils;
use rocket\ui\gui\field\BackableGuiField;
use rocket\op\ei\manage\gui\EiSiMaskId;

class EmbeddedOneToOneEiPropNature extends RelationEiPropNatureAdapter {

	public function __construct(ToOneEntityProperty $entityProperty, PropertyAccessProxy $accessProxy) {
		ArgUtils::assertTrue($entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_ONE);

		parent::__construct($entityProperty, $accessProxy,
				new RelationModel($this, false, false, RelationModel::MODE_EMBEDDED));

		$this->displayConfig = (new DisplayConfig(ViewMode::all()))
				->setSiStructureType(SiStructureType::SIMPLE_GROUP)
				->setDefaultDisplayedViewModes(ViewMode::bulky());
	}

	function buildEiField(Eiu $eiu): ?EiFieldNature {
		$targetEiuFrame = $eiu->frame()->forkSelect($eiu->prop(), $eiu->object())
				->frame()->exec($this->getRelationModel()->getTargetReadEiCmdPath());
		
		$field = new ToOneEiField($eiu, $targetEiuFrame, $this, $this->getRelationModel());
		$field->setMandatory($this->getRelationModel()->isMandatory());
		return $field;
	}

	function buildOutGuiField(Eiu $eiu): ?BackableGuiField {
		if ($eiu->guiDefinition()->isCompact()) {
			return $this->createCompactGuiField($eiu);
		}

		$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->object())->frame()
				->exec($this->getRelationModel()->getTargetReadEiCmdPath());

		$factory = new RelationGuiEmbeddedEntryFactory($targetEiuFrame, $this->relationModel->isReduced());

		$targetEiuEntry = $eiu->field()->getValue();
		$guiEmbeddedEntry = null;
		if ($targetEiuEntry !== null) {
			$guiEmbeddedEntry = $factory->createGuiEmbeddedEntryFromEiuEntry($targetEiuEntry);
		}

		return GuiFields::guiEmbeddedEntriesOut($targetEiuFrame->createSiFrame(), $this->relationModel->isReduced(),
				[$guiEmbeddedEntry]);
	}

	function buildInGuiField(Eiu $eiu): ?BackableGuiField {
		$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->object())->frame()
				->exec($this->getRelationModel()->getTargetEditEiCmdPath());


		$factory = new RelationGuiEmbeddedEntryFactory($targetEiuFrame, $this->relationModel->isReduced());

		$eiuMask = $targetEiuFrame->mask();
		$bulkySiMaskId = $eiuMask->createSiMaskId(ViewMode::BULKY_EDIT);
		$summarySiMaskId = null;
		if ($this->relationModel->isReduced()) {
			$summarySiMaskId = $eiuMask->createSiMaskId(ViewMode::COMPACT_READ);
		}

		$guiField = GuiFields::guiEmbeddedEntriesIn($targetEiuFrame->createSiFrame(), $factory,
				$bulkySiMaskId, $summarySiMaskId, $this->relationModel->isRemovable(), false,
				$this->relationModel->getMin(), $this->relationModel->getMax());

		$targetEiuEntry = $eiu->field()->getValue();
		if ($targetEiuEntry !== null) {
			$guiField->setValue([$factory->createGuiEmbeddedEntryFromEiuEntry($targetEiuEntry)]);
		}

		$guiField->setModel($eiu->field()->asGuiFieldModel(Mappers::valueClosure(
				function (array $guiEmbeddedEntries) use ($factory) {
					return ArrayUtils::current($factory->retrieveValidatedEiuEntries($guiEmbeddedEntries));
				})));

		return $guiField;
	}
	
	/**
	 * @param Eiu $eiu
	 * @return BackableGuiField
	 */
	private function createCompactGuiField(Eiu $eiu): BackableGuiField {
		$eiuEntry = $eiu->field()->getValue();
		
		if ($eiuEntry === null) {
			return GuiFields
					::out(SiCrumb::createLabel('0')->setSeverity(SiCrumb::SEVERITY_UNIMPORTANT))
					->setModel($eiu->field()->asGuiFieldModel());
		}
		
		CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
		
		return GuiFields::out(SiFields
				::crumbOut(
						SiCrumb::createIcon($eiuEntry->mask()->getIconType())->setSeverity(SiCrumb::SEVERITY_IMPORTANT),
						SiCrumb::createLabel($eiuEntry->object()->createIdentityString())))
				->setModel($eiu->field()->asGuiFieldModel());
	}
}