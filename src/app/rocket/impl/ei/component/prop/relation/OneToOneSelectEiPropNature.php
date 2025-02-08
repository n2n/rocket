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
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\adapter\config\EditAdapter;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\op\ei\util\Eiu;
use rocket\impl\ei\component\prop\relation\model\ToOneEiField;
use rocket\ui\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\model\gui\RelationLinkGuiField;
use rocket\impl\ei\component\prop\relation\model\gui\ToOneGuiField;
use rocket\impl\ei\component\prop\relation\model\filter\ToOneQuickSearchProp;
use rocket\op\ei\manage\critmod\quick\QuickSearchProp;
use rocket\impl\ei\component\prop\adapter\trait\QuickSearchConfigTrait;
use rocket\impl\ei\component\prop\adapter\EditConfigTrait;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\ui\gui\field\BackableGuiField;
use rocket\ui\gui\field\impl\GuiFields;
use n2n\util\type\CastUtils;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\ui\si\content\impl\SiFields;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\impl\ei\component\prop\relation\model\ToOneGuiFieldFactory;


class OneToOneSelectEiPropNature extends RelationEiPropNatureAdapter {
	use QuickSearchConfigTrait;

	public function __construct(RelationEntityProperty $entityProperty, PropertyAccessProxy $accessProxy) {
		ArgUtils::assertTrue($entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_ONE);

		parent::__construct($entityProperty, $accessProxy,
				new RelationModel($this, false, false, RelationModel::MODE_SELECT));

		$this->relationModel->setReadOnly(true);
	}
	
	function buildEiField(Eiu $eiu): ?EiFieldNature {
		$targetEiuFrame = $eiu->frame()->forkSelect($eiu->prop(), $eiu->object())
				->frame()->exec($this->getRelationModel()->getTargetReadEiCmdPath());
		
		$field = new ToOneEiField($eiu, $targetEiuFrame, $this, $this->getRelationModel());
		$field->setMandatory($this->relationModel->isMandatory());
		return $field;
	}
	
	function buildOutGuiField(Eiu $eiu): ?BackableGuiField {
		$factory = new ToOneGuiFieldFactory($this->relationModel);

		return $factory->createOutGuiField($eiu);
	}

	function buildInGuiField(Eiu $eiu): ?BackableGuiField {
		$factory = new ToOneGuiFieldFactory($this->relationModel);

		return $factory->createInGuiField($eiu);
	}

	private function readPickableQualifiers(Eiu $targetEiu, int $maxNum): ?array {
		if ($maxNum <= 0) {
			return null;
		}

		$num = $targetEiu->frame()->count();
		if ($num > $maxNum) {
			return null;
		}

		$siEntryQualifiers = [];
		foreach ($targetEiu->frame()->lookupObjects() as $eiuObject) {
			$siEntryQualifiers[] = $eiuObject->createSiEntryQualifier();
		}
		return $siEntryQualifiers;
	}
	
	function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		if (!$this->isQuickSearchable()) {
			return null;
		}
		
		$targetEiuEngine = $this->getRelationModel()->getTargetEiuEngine();
		$targetDefPropPaths = $targetEiuEngine->getIdNameDefinition()->getUsedDefPropPaths();
		
		if (empty($targetDefPropPaths)) {
			return null;
		}
		
		return new ToOneQuickSearchProp($this->getRelationModel(), $targetDefPropPaths, 
				$eiu->frame()->forkDiscover($eiu->prop()));
	}
}
