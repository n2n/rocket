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
use rocket\impl\ei\component\prop\relation\EmbeddedOneToManyEiProp;
use rocket\impl\ei\component\prop\ci\conf\ContentItemsEiPropConfigurator;
use rocket\impl\ei\component\prop\ci\model\ContentItem;
use rocket\impl\ei\component\prop\ci\model\PanelConfig;
use n2n\impl\persistence\orm\property\ToManyEntityProperty;
use rocket\impl\ei\component\prop\ci\model\ContentItemGuiField;
use rocket\impl\ei\component\prop\ci\model\ContentItemEditable;
use rocket\ei\EiPropPath;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\manage\gui\GuiField;
use rocket\ei\manage\gui\ui\DisplayItem;
use rocket\ei\manage\security\InaccessibleEiCommandPathException;

class ContentItemsEiProp extends EmbeddedOneToManyEiProp {
	private $panelConfigs = array();
// 	private $contentItemEiType;
	
	public function __construct() {
		parent::__construct();
		$this->displayConfig->setListReadModeDefaultDisplayed(false);
		$this->editConfig->setMandatory(false);
		$this->panelConfigs = array(new PanelConfig('main', 'Main', null, 0)); 
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		return new ContentItemsEiPropConfigurator($this/*, $this->eiPropRelation*/);
	}
	
	protected function getDisplayItemType(): string {
		return DisplayItem::TYPE_LIGHT_GROUP;
	}
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ToManyEntityProperty);
		
		$targetEntityModelClass = $entityProperty->getRelation()->getTargetEntityModel()->getClass();
		ArgUtils::assertTrue($targetEntityModelClass->isSubclassOf(ContentItem::getClass()) || 
				$targetEntityModelClass->getName() === ContentItem::class);
		
		parent::setEntityProperty($entityProperty);
	}
	
	/**
	 * @return \rocket\ei\EiPropPath
	 */
	public static function getPanelEiPropPath(): EiPropPath {
		return new EiPropPath(array('panel'));
	}
	
	/**
	 * @return \rocket\ei\EiPropPath
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
	
	public function hasPanelConfigs() {
		return !empty($this->getPanelConfigs());
	}
	
	public function getPanelConfigs(): array {
		return $this->panelConfigs;
	}
	
	/**
	 * @param PanelConfig[] $panelConfigs
	 */
	public function setPanelConfigs(array $panelConfigs) {
		ArgUtils::valArray($panelConfigs, PanelConfig::class);
		$this->panelConfigs = $panelConfigs;
	}
	
	public function determinePanelConfigs(Eiu $eiu) {
		return $this->panelConfigs;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiField()
	 */
	public function buildGuiField(Eiu $eiu): ?GuiField {
		$mapping = $eiu->entry()->getEiEntry();
	
		$eiFrame = $eiu->frame()->getEiFrame();
		$relationEiField = $mapping->getEiField(EiPropPath::from($this));
		try {
			$targetReadEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame, $mapping);
		} catch (InaccessibleEiCommandPathException $e) {
			return null;
		}
		
		$panelConfigs = $this->determinePanelConfigs($eiu);
	
		$contentItemEditable = null;
		if (!$this->eiPropRelation->isReadOnly($mapping, $eiFrame)) {
			$targetEditEiFrame = $this->eiPropRelation->createTargetEditPseudoEiFrame($eiFrame, $mapping);
				
			$contentItemEditable = new ContentItemEditable($this->getLabelLstr(), $relationEiField, $targetReadEiFrame,
					$targetEditEiFrame, $panelConfigs);
			
			$draftMode = $mapping->getEiObject()->isDraft();
			$contentItemEditable->setDraftMode($draftMode);
			$contentItemEditable->setReduced($this->isReduced());
				
			if ($targetEditEiFrame->getEiExecution()->isGranted()) {
				$contentItemEditable->setNewMappingFormUrl($this->eiPropRelation->buildTargetNewEiuEntryFormUrl($mapping,
						$draftMode, $eiFrame, $eiu->frame()->getHttpContext()));
			}
		}
		
		return new ContentItemGuiField($this->getLabelLstr(), $this->determinePanelConfigs($eiu), 
				$relationEiField, $targetReadEiFrame, $eiu->gui()->isCompact(), $contentItemEditable);
	}
}
