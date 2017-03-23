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
use rocket\spec\ei\component\field\impl\relation\EmbeddedOneToManyEiField;

use rocket\spec\ei\component\field\impl\ci\conf\ContentItemsEiFieldConfigurator;
use rocket\spec\ei\component\field\impl\ci\model\ContentItem;
use rocket\spec\ei\component\field\impl\ci\model\PanelConfig;
use n2n\impl\persistence\orm\property\ToManyEntityProperty;
use rocket\spec\ei\component\field\impl\ci\model\ContentItemGuiElement;
use rocket\spec\ei\component\field\impl\ci\model\ContentItemEditable;
use rocket\spec\ei\manage\gui\GuiElement;
use rocket\spec\ei\EiSpec;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use rocket\spec\ei\manage\EiFrame;

class ContentItemsEiField extends EmbeddedOneToManyEiField {
	private $panelConfigs = array();
// 	private $contentItemEiSpec;
	
	public function __construct() {
		parent::__construct();
		$this->displayDefinition->setListReadModeDefaultDisplayed(false);
		$this->standardEditDefinition->setMandatory(false);
		$this->panelConfigs = array(new PanelConfig('main', 'Main', null, 0)); 
	}
	
	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new ContentItemsEiFieldConfigurator($this/*, $this->eiFieldRelation*/);
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ToManyEntityProperty
				&& $entityProperty->getRelation()->getTargetEntityModel()->getClass()->getName() === ContentItem::class);
		parent::setEntityProperty($entityProperty);
	}
	
	/**
	 * @return \rocket\spec\ei\EiFieldPath
	 */
	public static function getPanelEiFieldPath(): EiFieldPath {
		return new EiFieldPath(array('panel'));
	}
	
	/**
	 * @return \rocket\spec\ei\EiFieldPath
	 */
	public static function getOrderIndexEiFieldPath(): EiFieldPath {
		return new EiFieldPath(array('orderIndex'));
	}
	
// 	public function setContentItemEiSpec(EiSpec $contentItemEiSpec) {
// 		$this->contentItemEiSpec = $contentItemEiSpec;
// 	}
	
// 	public function getContentItemEiSpec(): EiSpec {
// 		if ($this->contentItemEiSpec === null) {
// 			return $this->contentItemEiSpec;
// 		}
		
// 		throw new IllegalStateException('Undefined ContentItem EiSpec.');
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
	 * @see \rocket\spec\ei\manage\gui\GuiField::buildGuiElement()
	 */
	public function buildGuiElement(Eiu $eiu) {
		$mapping = $eiu->entry()->getEiMapping();
	
		$eiFrame = $eiu->frame()->getEiFrame();
		$relationMappable = $mapping->getMappable(EiFieldPath::from($this));
		$targetReadEiFrame = $this->eiFieldRelation->createTargetReadPseudoEiFrame($eiFrame, $mapping);
		$panelConfigs = $this->determinePanelConfigs($eiu);
	
		$contentItemEditable = null;
		if (!$this->eiFieldRelation->isReadOnly($mapping, $eiFrame)) {
			$targetEditEiFrame = $this->eiFieldRelation->createTargetEditPseudoEiFrame($eiFrame, $mapping);
				
			$contentItemEditable = new ContentItemEditable($this->getLabelLstr(), $relationMappable, $targetReadEiFrame,
					$targetEditEiFrame, $panelConfigs);
	
			$draftMode = $mapping->getEiEntry()->isDraft();
			$contentItemEditable->setDraftMode($draftMode);
				
			if ($targetEditEiFrame->getEiExecution()->isGranted()) {
				$contentItemEditable->setNewMappingFormUrl($this->eiFieldRelation->buildTargetNewEntryFormUrl($mapping,
						$draftMode, $eiFrame, $eiu->frame()->getHttpContext()));
			}
		}
		
		return new ContentItemGuiElement($this->getLabelLstr(), $this->determinePanelConfigs($eiu), 
				$relationMappable, $targetReadEiFrame, $contentItemEditable);
	}
}
