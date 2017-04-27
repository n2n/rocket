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
namespace rocket\spec\ei\component\field\impl\ci;

use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\component\field\impl\relation\EmbeddedOneToManyEiProp;
use rocket\spec\ei\component\field\impl\ci\conf\ContentItemsEiPropConfigurator;
use rocket\spec\ei\component\field\impl\ci\model\ContentItem;
use rocket\spec\ei\component\field\impl\ci\model\PanelConfig;
use n2n\impl\persistence\orm\property\ToManyEntityProperty;
use rocket\spec\ei\component\field\impl\ci\model\ContentItemGuiField;
use rocket\spec\ei\component\field\impl\ci\model\ContentItemEditable;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\field\indepenent\EiPropConfigurator;
use rocket\spec\ei\manage\EiFrame;

class ContentItemsEiProp extends EmbeddedOneToManyEiProp {
	private $panelConfigs = array();
// 	private $contentItemEiType;
	
	public function __construct() {
		parent::__construct();
		$this->displayDefinition->setListReadModeDefaultDisplayed(false);
		$this->standardEditDefinition->setMandatory(false);
		$this->panelConfigs = array(new PanelConfig('main', 'Main', null, 0)); 
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		return new ContentItemsEiPropConfigurator($this/*, $this->eiPropRelation*/);
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ToManyEntityProperty
				&& $entityProperty->getRelation()->getTargetEntityModel()->getClass()->getName() === ContentItem::class);
		parent::setEntityProperty($entityProperty);
	}
	
	/**
	 * @return \rocket\spec\ei\EiPropPath
	 */
	public static function getPanelEiPropPath(): EiPropPath {
		return new EiPropPath(array('panel'));
	}
	
	/**
	 * @return \rocket\spec\ei\EiPropPath
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
	
	public function hasPanelConfigs(): bool {
		return !empty($this->getPanelConfigs());
	}
	
	public function getPanelConfigs(): array {
		return $this->panelConfigs;
	}
	
	public function setPanelConfigs(array $panelConfigs) {
		ArgUtils::valArray($panelConfigs, PanelConfig::class);
		$this->panelConfigs = $panelConfigs;
	}
	
	public function determinePanelConfigs(Eiu $eiu) {
		return $this->panelConfigs;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiProp::buildGuiField()
	 */
	public function buildGuiField(Eiu $eiu) {
		$mapping = $eiu->entry()->getEiEntry();
	
		$eiFrame = $eiu->frame()->getEiFrame();
		$relationEiField = $mapping->getEiField(EiPropPath::from($this));
		$targetReadEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame, $mapping);
		$panelConfigs = $this->determinePanelConfigs($eiu);
	
		$contentItemEditable = null;
		if (!$this->eiPropRelation->isReadOnly($mapping, $eiFrame)) {
			$targetEditEiFrame = $this->eiPropRelation->createTargetEditPseudoEiFrame($eiFrame, $mapping);
				
			$contentItemEditable = new ContentItemEditable($this->getLabelLstr(), $relationEiField, $targetReadEiFrame,
					$targetEditEiFrame, $panelConfigs);
	
			$draftMode = $mapping->getEiObject()->isDraft();
			$contentItemEditable->setDraftMode($draftMode);
				
			if ($targetEditEiFrame->getEiExecution()->isGranted()) {
				$contentItemEditable->setNewMappingFormUrl($this->eiPropRelation->buildTargetNewEntryFormUrl($mapping,
						$draftMode, $eiFrame, $eiu->frame()->getHttpContext()));
			}
		}
		
		return new ContentItemGuiField($this->getLabelLstr(), $this->determinePanelConfigs($eiu), 
				$relationEiField, $targetReadEiFrame, $contentItemEditable);
	}
}
