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
namespace rocket\impl\ei\component\prop\ci\model;

use rocket\ui\gui\field\GuiField;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ui\si\content\impl\SiFields;
use n2n\util\type\CastUtils;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\op\ei\util\gui\EiuGuiEntry;
use rocket\ui\si\content\SiField;
use rocket\ui\si\content\impl\relation\SiPanelInput;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\content\impl\relation\SiPanel;
use rocket\ui\si\content\impl\relation\EmbeddedEntryPanelInputHandler;
use rocket\ui\si\content\impl\relation\EmbeddedEntryPanelsInSiField;
use rocket\ui\gui\field\GuiFieldMap;
use rocket\impl\ei\component\prop\relation\model\gui\EmbeddedGuiCollection;
use rocket\op\ei\EiPropPath;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\ui\si\content\SiFieldModel;
use n2n\core\container\N2nContext;
use rocket\op\ei\manage\gui\EiSiMaskId;
use rocket\ui\gui\ViewMode;
use rocket\impl\ei\component\prop\relation\model\gui\RelationGuiEmbeddedEntryFactory;
use rocket\ui\gui\field\impl\GuiFields;
use rocket\ui\gui\field\impl\relation\GuiPanel;
use n2n\bind\mapper\impl\Mappers;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use rocket\ui\gui\field\BackableGuiField;
use rocket\ui\si\content\impl\meta\SiCrumbGroup;
use rocket\ui\si\content\impl\meta\SiCrumb;

class ContentItemGuiFieldFactory {
	/**
	 * @param RelationModel $relationModel
	 * @param PanelDeclaration[] $panelDeclarations
	 */
	function __construct(private RelationModel $relationModel, private array $panelDeclarations) {
		ArgUtils::valArray($this->panelDeclarations, PanelDeclaration::class);
	}

	function createOutGuiField(Eiu $eiu): BackableGuiField {
		$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->object())->frame()
				->exec($this->relationModel->getTargetReadEiCmdPath());

		$factory = new RelationGuiEmbeddedEntryFactory($targetEiuFrame, $this->relationModel->isReduced());

		$guiField = GuiFields::embeddedEntriesPanelsOut($targetEiuFrame->createSiFrame());

		$guiField->setPanels(array_values($this->createGuiPanels($eiu, $targetEiuFrame, $factory, true)));

		return $guiField;
	}

	function createInGuiField(Eiu $eiu): BackableGuiField {
		$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->object())->frame()
				->exec($this->relationModel->getTargetEditEiCmdPath());

		$factory = new RelationGuiEmbeddedEntryFactory($targetEiuFrame, $this->relationModel->isReduced());

		$guiField = GuiFields::embeddedEntriesPanelsIn($targetEiuFrame->createSiFrame());

		$guiField->setPanels(array_values($this->createGuiPanels($eiu, $targetEiuFrame, $factory, false)));

		$guiField->setModel($eiu->field()->asGuiFieldModel(Mappers
				::valueClosure(fn (array $guiPanels) => $this->handleSave($guiPanels, $factory))));

		return $guiField;
	}

	private function createGuiPanels(Eiu $eiu, EiuFrame $targetEiuFrame, RelationGuiEmbeddedEntryFactory $factory, bool $readOnly): array {
		$guiPanels = [];
		foreach ($this->panelDeclarations as $panelDeclaration) {
			$guiPanels[$panelDeclaration->getName()] = new GuiPanel($panelDeclaration->getName(),
					$panelDeclaration->getLabel(),
					$targetEiuFrame->mask()->createSiMaskId($readOnly ? ViewMode::BULKY_READ : ViewMode::BULKY_EDIT),
					($this->relationModel->isReduced() ? $targetEiuFrame->mask()->createSiMaskId(ViewMode::COMPACT_READ) : null),
					$factory);
		}

		foreach ($this->sort($eiu->field()->getValue()) as $eiuEntry) {
			assert($eiuEntry instanceof EiuEntry);
			$panelName = $eiuEntry->getScalarValue('panel');
			if (isset($guiPanels[$panelName])) {
				$guiPanels[$panelName]->addGuiEmbeddedEntry($factory->createGuiEmbeddedEntryFromEiuEntry($eiuEntry));
			}
		}

		return $guiPanels;
	}

	/**
	 * @param GuiPanel[] $guiPanels
	 * @return EiuEntry[]
	 * @throws ValueIncompatibleWithConstraintsException
	 */
	private function handleSave(array $guiPanels, RelationGuiEmbeddedEntryFactory $factory): array {
		$eiuEntries = [];
		foreach ($guiPanels as $guiPanel) {
			$panelName = $guiPanel->getSiPanel()->getName();
			$i = 0;
			foreach ($guiPanel->getGuiEmbeddedEntries() as $guiEmbeddedEntry) {
				$panelEiuEntry = $factory->retrieveEiuEntriesItem($guiEmbeddedEntry)->validatedEiuEntry;
				$panelEiuEntry->setValue('panel', $panelName);
				$i += 10;
				$panelEiuEntry->setScalarValue('orderIndex', $i);
				$eiuEntries[] = $panelEiuEntry;
			}
		}
		return $eiuEntries;
	}


	/**
	 * @param EiuEntry[] $eiuEntries
	 * @return EiuEntry[]
	 */
	private function sort(array $eiuEntries): array {
		uasort($eiuEntries, function(EiuEntry $a, EiuEntry $b) {
			$aValue = $a->getScalarValue('orderIndex');
			$bValue = $b->getScalarValue('orderIndex');

			if ($aValue == $bValue) {
				return 0;
			}

			return ($aValue < $bValue) ? -1 : 1;
		});
		return $eiuEntries;
	}

	function createCompactGuiField(Eiu $eiu): BackableGuiField {
		$siCrumbGroups = [];

		foreach ($this->panelDeclarations as $panelDeclaration) {
			$siCrumbGroups[$panelDeclaration->getName()] = new SiCrumbGroup([]);
		}

		foreach ($eiu->field()->getValue() as $eiuEntry) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);

			$panelName = $eiuEntry->getScalarValue('panel');
			if (isset($siCrumbGroups[$panelName])) {
				$siCrumbGroups[$panelName]->add(SiCrumb::createIcon($eiuEntry->mask()->getIconType())
						->setTitle($eiuEntry->createIdentityString())
						->setSeverity(SiCrumb::SEVERITY_IMPORTANT));
			}
		}

		foreach ($siCrumbGroups as $siCrumbGroup) {
			if ($siCrumbGroup->isEmpty()) {
				$siCrumbGroup->add(SiCrumb::createLabel('0')->setSeverity(SiCrumb::SEVERITY_UNIMPORTANT));
			}
		}

		return GuiFields::out(SiFields::crumbOut()->setGroups($siCrumbGroups));
	}


}