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
use rocket\op\ei\util\Eiu;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\op\ei\manage\critmod\quick\QuickSearchProp;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\impl\ei\component\prop\relation\model\ToOneEiField;
use rocket\ui\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\model\gui\RelationLinkGuiField;
use rocket\impl\ei\component\prop\relation\model\gui\ToOneGuiField;
use rocket\impl\ei\component\prop\relation\model\filter\ToOneQuickSearchProp;
use rocket\impl\ei\component\prop\adapter\trait\QuickSearchConfigTrait;
use rocket\op\ei\manage\security\filter\SecurityFilterProp;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\ui\gui\field\BackableGuiField;
use rocket\impl\ei\component\prop\relation\model\ToOneGuiFieldFactory;

class ManyToOneSelectEiPropNature extends RelationEiPropNatureAdapter {
	use QuickSearchConfigTrait;

	public function __construct(RelationEntityProperty $entityProperty, PropertyAccessProxy $accessProxy) {
		ArgUtils::assertTrue($entityProperty->getType() === RelationEntityProperty::TYPE_MANY_TO_ONE);

		parent::__construct($entityProperty, $accessProxy,
				new RelationModel($this, true, false, RelationModel::MODE_SELECT));

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

	
	public function buildSecurityFilterProp(Eiu $eiu): ?SecurityFilterProp {
		return null;
//		$eiEntryFilterProp = parent::createSecurityFilterProp($eiu);
//		CastUtils::assertTrue($eiEntryFilterProp instanceof ToOneSecurityFilterProp);
//
//		$eiEntryFilterProp->setTargetSelectToolsUrlCallback(function () use ($eiu) {
//			return GlobalOverviewJhtmlController::buildToolsAjahUrl(
//					$eiu->lookup(ScrRegistry::class), $this->eiPropRelation->getTargetEiType(),
//					$this->eiPropRelation->getTargetEiMask());
//		});
//
//		return $eiEntryFilterProp;
	}
	
	public function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		if (!$this->isQuickSearchable()) {
			return null;
		}
		
		$targetEiuEngine = $this->getRelationModel()->getTargetEiuEngine();
		$targetDefPropPaths = $targetEiuEngine->getIdNameDefinition()->getUsedDefPropPaths();
		
		if (empty($targetDefPropPaths)) {
			return null;
		}
		
		$targetEiu = $eiu->frame()->forkDiscover($eiu->prop());
		$targetEiu->frame()->exec($this->getRelationModel()->getTargetReadEiCmdPath());
		return new ToOneQuickSearchProp($this->getRelationModel(), $targetDefPropPaths, $targetEiu);
	}

	

}

// class SimpleToOneDraftValueSelection extends SimpleDraftValueSelection {
// 	private $em;
// 	private $targetEntityModel;
	
// 	public function __construct($columnAlias, EntityManager $em, EntityModel $targetEntityModel) {
// 		parent::__construct($columnAlias);
// 		$this->em = $em;
// 		$this->targetEntityModel = $targetEntityModel;
// 	}

// 	/* (non-PHPdoc)
// 	 * @see \rocket\op\ei\manage\draft\DraftValueSelection::buildDraftValue()
// 	 */
// 	public function buildDraftValue() {
// 		if ($this->rawValue === null) return null;
		
// 		$targetId = $this->targetEntityModel->getIdDef()->getEntityProperty()->parseValue($this->rawValue, 
// 				$this->em->getPdo());
// 		return $this->em->find($this->targetEntityModel->getClass(), $targetId);
// 	}
// }
