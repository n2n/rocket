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
namespace rocket\impl\ei\component\prop\relation\model\gui;

use rocket\ui\gui\field\GuiField;
use rocket\ui\si\content\SiField;
use rocket\op\ei\util\Eiu;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ui\si\content\impl\SiFields;
use n2n\util\type\CastUtils;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\ui\si\content\impl\relation\ObjectQualifiersSelectInSiField;
use rocket\ui\gui\field\GuiFieldMap;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\ui\gui\field\BackableGuiField;
use rocket\ui\si\content\SiObjectQualifier;
use rocket\ui\gui\field\impl\GuiFields;
use n2n\bind\mapper\impl\Mappers;
use rocket\ui\gui\ViewMode;

class ToManyGuiFieldFactory{

	/**
	 * @param RelationModel $relationModel
	 */
	function __construct(private RelationModel $relationModel) {
	}

	function createOutGuiField(Eiu $eiu): BackableGuiField {
		$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->entry())->frame();
		$targetEiuFrame->exec($this->relationModel->getTargetReadEiCmdPath());

		$num = $targetEiuFrame->count();
		$label = null;
		if ($num == 1) {
			$label = $num . ' ' . $targetEiuFrame->engine()->mask()->getLabel();
		} else {
			$label = $num . ' ' . $targetEiuFrame->engine()->mask()->getPluralLabel();
		}

		if (null !== ($overviewNavPoint = $targetEiuFrame->getOverviewNavPoint(false))) {
			return GuiFields::out(SiFields::linkOut($overviewNavPoint, $label));
		}

		return GuiFields::out(SiFields::stringOut($label));
	}

	function createInGuiField(Eiu $eiu): BackableGuiField {
		$targetEiu = $eiu->frame()->forkSelect($eiu->prop()->getPath(), $eiu->object());
		$targetEiu->frame()->exec($this->relationModel->getTargetReadEiCmdPath());

		return GuiFields::objectQualifiersSelectIn($targetEiu->frame()->createSiFrame(),
						$targetEiu->mask()->createSiMaskId(ViewMode::COMPACT_READ),
						$this->relationModel->getMin(), $this->relationModel->getMax(),
						$this->readPickableQualifiers($targetEiu, $this->relationModel->getMaxPicksNum()))
				->setValue($this->readValues($eiu))
				->setModel($eiu->field()->asGuiFieldModel(
						Mappers::valueClosure(fn ($v) => $this->mapInput($v, $targetEiu))));
	}

	/**
	 * @param Eiu $targetEiu
	 * @param int $maxNum
	 * @return SiObjectQualifier[]|null
	 */
	private function readPickableQualifiers(Eiu $targetEiu, int $maxNum): ?array {
		if ($maxNum <= 0) {
			return null;
		}

		$targetEiuFrame = $targetEiu->frame();

		$num = $targetEiuFrame->count();
		if ($num > $maxNum) {
			return null;
		}
		
		$siEntryQualifiers = [];
		foreach ($targetEiuFrame->lookupObjects() as $eiuObject) {
			$siEntryQualifiers[] = $eiuObject->createSiObjectQualifier();
		}
		return $siEntryQualifiers;
	}

	/**
	 * @param Eiu $eiu
	 * @return SiObjectQualifier[]
	 */
	private function readValues(Eiu $eiu): array {
		$values = [];
		foreach ($eiu->field()->getValue() as $eiuEntry) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$values[] = $eiuEntry->createSiObjectQualifier();
		}
		
		return $values;
	}

	function mapInput(array $siObjectQualifiers, Eiu $targetEiu): array {
		$targetEiuFrame = $targetEiu->frame();

		$values = [];
		foreach ($this->siField->getValues() as $siQualifier) {
			$id = $targetEiuFrame->siQualifierToId($siQualifier);
			$values[] = $targetEiuFrame->lookupEntry($id);
		}
		return $values;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\gui\field\GuiField::getSiField()
	 */
	function getSiField(): SiField {
		return $this->siField;
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
}