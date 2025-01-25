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
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\impl\persistence\orm\property\ToManyEntityProperty;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\ui\gui\ViewMode;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\impl\ei\component\prop\relation\model\ToManyEiField;
use rocket\ui\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\model\gui\SiEmbeddedToManyGuiField;
use n2n\util\type\CastUtils;
use rocket\ui\si\content\impl\meta\SiCrumb;
use rocket\ui\si\content\impl\SiFields;
use rocket\op\ei\util\entry\EiuEntry;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\ui\si\meta\SiStructureType;
use rocket\ui\gui\field\impl\GuiFields;
use rocket\ui\gui\field\impl\relation\GuiEmbeddedEntriesCollection;
use rocket\impl\ei\component\prop\relation\model\gui\RelationGuiEmbeddedEntryFactory;
use n2n\bind\mapper\impl\Mappers;
use rocket\ui\gui\field\BackableGuiField;

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


	function buildOutGuiField(Eiu $eiu): BackableGuiField {
		if ($eiu->guiDefinition()->isCompact()) {
			return $this->createCompactGuiField($eiu);
		}

		$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->object())->frame()
				->exec($this->getRelationModel()->getTargetReadEiCmdPath());

		$factory = new RelationGuiEmbeddedEntryFactory($targetEiuFrame, $this->relationModel->isReduced());

		return GuiFields::guiEmbeddedEntriesOut($targetEiuFrame->createSiFrame(), $this->relationModel->isReduced(),
				$factory->createGuiEmbeddedEntriesFromEiuEntries($eiu->field()->getValue()));
	}

	function buildInGuiField(Eiu $eiu): ?BackableGuiField {

		$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->object())->frame()
				->exec($this->getRelationModel()->getTargetReadEiCmdPath());

		$sortable = ($this->relationModel->getMax() === null || $this->relationModel->getMax() > 1)
				&& $this->relationModel->getTargetOrderEiPropPath() !== null;

		$factory = new RelationGuiEmbeddedEntryFactory($targetEiuFrame, $this->relationModel->isReduced());

		$guiField = GuiFields::guiEmbeddedEntriesIn($targetEiuFrame->createSiFrame(), $factory,
				$this->relationModel->isReduced(), $this->relationModel->isRemovable(), $sortable,
				$this->relationModel->getMin(), $this->relationModel->getMax());

		$guiField->setValue($factory->createGuiEmbeddedEntriesFromEiuEntries($eiu->field()->getValue()));

		$guiField->setModel($eiu->field()->asGuiFieldModel(Mappers::valueClosure(
				function (array $guiEmbeddedEntries) use ($factory) {
					return $factory->retrieveEiuEntries($guiEmbeddedEntries);
				})));

		return $guiField;
	}

	private function createCompactGuiField(Eiu $eiu): BackableGuiField {
		$siCrumbs = [];
		foreach ($eiu->field()->getValue() as $eiuEntry) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$siCrumbs[] = SiCrumb::createIcon($eiuEntry->mask()->getIconType())
					->setTitle($eiuEntry->createIdentityString())
					->setSeverity(SiCrumb::SEVERITY_IMPORTANT);
		}
		
		return GuiFields::out(SiFields::crumbOut(...$siCrumbs));
	}
}