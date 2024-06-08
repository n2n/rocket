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
namespace rocket\impl\ei\component\prop\ci;

use n2n\persistence\orm\property\EntityProperty;
use n2n\util\type\ArgUtils;
use rocket\impl\ei\component\prop\ci\model\ContentItem;
use n2n\impl\persistence\orm\property\ToManyEntityProperty;
use rocket\impl\ei\component\prop\ci\model\ContentItemGuiField;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\util\Eiu;
use rocket\ui\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\ui\gui\ViewMode;
use rocket\impl\ei\component\prop\adapter\config\EditAdapter;
use rocket\impl\ei\component\prop\relation\RelationEiPropNatureAdapter;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\impl\ei\component\prop\relation\model\ToManyEiField;
use rocket\ui\si\content\impl\SiFields;
use rocket\op\ei\util\entry\EiuEntry;
use n2n\util\type\CastUtils;
use rocket\ui\si\content\impl\meta\SiCrumb;
use rocket\ui\si\content\impl\meta\SiCrumbGroup;
use rocket\impl\ei\component\prop\ci\model\PanelDeclaration;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\ui\si\meta\SiStructureType;

class ContentItemsEiPropNature extends RelationEiPropNatureAdapter {
	
//	private $contentItemsConfig;

	/**
 	 * @var PanelDeclaration[]
 	 */
	private $panelDeclarations = array();
	
	public function __construct(RelationEntityProperty $entityProperty, PropertyAccessProxy $accessProxy) {
		parent::__construct($entityProperty, $accessProxy,
				new RelationModel($this, false, true, RelationModel::MODE_EMBEDDED));

		$this->displayConfig = (new DisplayConfig(ViewMode::all()))
				->setSiStructureType(SiStructureType::SIMPLE_GROUP)
				->setListReadModeDefaultDisplayed(false);
		
//		$this->contentItemsConfig = new ContentItemsConfig();
		$this->panelDeclarations = array(new PanelDeclaration('main', 'Main', null, 0));


//		$this->setup(
//				(new DisplayConfig(ViewMode::all()))->setListReadModeDefaultDisplayed(false),
//				new RelationModel($this, false, true, RelationModel::MODE_EMBEDDED,
//						(new EditAdapter())->setMandatoryChoosable(false)->setMandatory(false)));
		
// 		$this->configurator = new ContentItemsEiPropConfigurator($this/*, $this->eiPropRelation*/);
// 		$this->configurator->registerDisplayConfig($this->displayConfig);
// 		$this->configurator->registerEditConfig($this->editConfig);
// 		$this->configurator->setRelationModel($this->getRelationModel());
	}


//	/**
//	 * @return bool
//	 */
//	function hasPanelDeclarations() {
//		return !empty($this->panelDeclarations);
//	}
//
//	/**
//	 * @return PanelDeclaration[]
//	 */
//	function getPanelDeclarations() {
//		return $this->panelDeclarations;
//	}

	/**
	 * @param PanelDeclaration[] $panelDeclarations
	 */
	function setPanelDeclarations(array $panelDeclarations) {
		ArgUtils::valArray($panelDeclarations, PanelDeclaration::class);
		$this->panelDeclarations = $panelDeclarations;
	}
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ToManyEntityProperty);
		
		$targetEntityModelClass = $entityProperty->getRelation()->getTargetEntityModel()->getClass();
		ArgUtils::assertTrue($targetEntityModelClass->isSubclassOf(ContentItem::getClass()) || 
				$targetEntityModelClass->getName() === ContentItem::class);
		
		parent::setEntityProperty($entityProperty);
	}
	
	/**
	 * @return \rocket\op\ei\EiPropPath
	 */
	public static function getPanelEiPropPath(): EiPropPath {
		return new EiPropPath(array('panel'));
	}
	
	/**
	 * @return \rocket\op\ei\EiPropPath
	 */
	public static function getOrderIndexEiPropPath(): EiPropPath {
		return new EiPropPath(array('orderIndex'));
	}
	
// 	public function setContentItemEiType(EiType $contentItemEiType) {
// 		$this->contentItemEiType = $contentItemEiType;
// 	}
	
// 	public function getContentItemEiType(): EiType {
// 		if ($this->contentItemEiType === null) {
// 			return $this->contentItemEiType;
// 		}
		
// 		throw new IllegalStateException('Undefined ContentItem EiType.');
// 	}
	
	
	/**
	 * @param Eiu $eiu
	 * @return \rocket\impl\ei\component\prop\ci\model\PanelDeclaration[]
	 */
	public function determinePanelDeclarations(Eiu $eiu) {
		return $this->panelDeclarations;
	}
	
	function buildEiField(Eiu $eiu): ?EiFieldNature {
		$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->object())
				->frame()->exec($this->getRelationModel()->getTargetReadEiCmdPath());
		
		return new ToManyEiField($eiu, $targetEiuFrame, $this, $this->getRelationModel());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\gui\GuiProp::buildGuiField()
	 */
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {	
		$readOnly = $readOnly || $this->relationModel->isReadOnly();

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
			
		return new ContentItemGuiField($eiu, $targetEiuFrame, $this->getRelationModel(), 
				$this->determinePanelDeclarations($eiu), $readOnly);
	}
	
	/**
	 * @param Eiu $eiu
	 * @return \rocket\ui\si\content\SiField
	 */
	private function createCompactGuiField(Eiu $eiu) {
		$siCrumbGroups = [];
		
		foreach ($this->determinePanelDeclarations($eiu) as $panelDeclaration) {
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
		
		return $eiu->factory()->newGuiField(SiFields::crumbOut()->setGroups($siCrumbGroups))->toGuiField();
	}
}
