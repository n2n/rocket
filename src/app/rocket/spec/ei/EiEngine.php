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
namespace rocket\spec\ei;

use n2n\core\container\N2nContext;
use rocket\spec\ei\component\CritmodFactory;
use rocket\spec\ei\component\SecurityFactory;
use rocket\spec\ei\manage\draft\stmt\DraftMetaInfo;
use rocket\spec\ei\component\GuiFactory;
use rocket\spec\ei\component\MappingFactory;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\EiEntry;
use rocket\spec\ei\component\modificator\EiModificatorCollection;
use rocket\spec\ei\component\command\EiCommandCollection;
use rocket\spec\ei\component\field\EiFieldCollection;
use rocket\spec\ei\manage\critmod\filter\FilterDefinition;
use rocket\spec\ei\manage\critmod\sort\SortDefinition;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\gui\GuiDefinition;
use rocket\spec\ei\component\DraftDefinitionFactory;
use rocket\spec\ei\manage\draft\DraftDefinition;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\component\field\GenericEiField;
use rocket\spec\ei\component\field\ScalarEiField;
use rocket\spec\ei\manage\generic\ScalarEiDefinition;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\generic\ScalarEiProperty;
use rocket\spec\ei\manage\generic\GenericEiProperty;
use rocket\spec\ei\manage\generic\GenericEiDefinition;
use rocket\spec\ei\manage\critmod\quick\QuickSearchDefinition;
use rocket\spec\ei\manage\util\model\EiuEntry;

class EiEngine {
	private $eiSpec;
	private $eiMask;
	private $eiFieldCollection;
	private $eiCommandCollection;
	private $eiModificatorCollection;
	
	private $guiDefinition;
	private $draftDefinition;
	private $genericEiDefinition;
	private $scalarEiDefinition;
		
	public function __construct(EiSpec $eiSpec, EiMask $eiMask = null) {
		$this->eiSpec = $eiSpec;
		$this->eiMask = $eiMask;
		$this->eiFieldCollection = new EiFieldCollection($this);
		$this->eiCommandCollection = new EiCommandCollection($this);
		$this->eiModificatorCollection = new EiModificatorCollection($this);
	}
	
	public function clear() {
		$this->guiDefinition = null;
		$this->draftDefinition = null;
		$this->genericEiDefinition = null;
		$this->scalarEiDefinition = null;
	}
	
	/**
	 * @return \rocket\spec\ei\EiSpec
	 */
	public function getEiSpec() {
		return $this->eiSpec;
	}
	
	/**
	 * @return \rocket\spec\ei\mask\EiMask
	 */
	public function getEiMask() {
		return $this->eiMask;
	}
		
	public function getEiThing() {
		if ($this->eiMask !== null) {
			return $this->eiMask;
		}
		return $this->eiSpec;
	}
	
	public function getSupremeEiEngine() {
		return $this->getSupremeEiThing()->getEiEngine();
	}
	
	public function getSupremeEiThing() {
		$supremeEiSpec = $this->eiSpec->getSupremeEiSpec();
		if (null !== $this->eiMask) {
			return $this->eiMask->determineEiMask($supremeEiSpec);
		}
		return $supremeEiSpec;
	}
	
	public function getEiFieldCollection(): EiFieldCollection {
		return $this->eiFieldCollection;
	}
	
	public function getEiCommandCollection(): EiCommandCollection {
		return $this->eiCommandCollection;
	}
	
	public function getEiModificatorCollection(): EiModificatorCollection {
		return $this->eiModificatorCollection;
	}

	private $critmodFactory;
	
	private function getCritmodFactory() {
		if ($this->critmodFactory === null) {
			$this->critmodFactory = new CritmodFactory($this->eiFieldCollection, 
					$this->eiModificatorCollection);
		}
		
		return $this->critmodFactory;
	}
	
	public function createManagedFilterDefinition(EiFrame $eiFrame): FilterDefinition {
		return $this->getCritmodFactory()->createManagedFilterDefinition($eiFrame);
	}
	
	public function createFilterDefinition(N2nContext $n2nContext): FilterDefinition {
		return $this->getCritmodFactory()->createFilterDefinition($n2nContext);
	}
	
	public function createEiMappingFilterDefinition(N2nContext $n2nContext) {
		return $this->getCritmodFactory()->createEiMappingFilterDefinition($n2nContext);
	}
	
	public function createManagedSortDefinition(EiFrame $eiFrame): SortDefinition {
		return $this->getCritmodFactory()->createManagedSortDefinition($eiFrame);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\mask\EiMask::createSortDefinition($n2nContext)
	 */
	public function createSortDefinition(N2nContext $n2nContext): SortDefinition {
		return $this->getCritmodFactory()->createSortDefinition($n2nContext);
	}
	
	public function createQuickSearchDefinition(EiFrame $eiFrame): QuickSearchDefinition {
		return $this->getCritmodFactory()->createQuickSearchDefinition($eiFrame);
	}
	
	public function createPrivilegeDefinition(N2nContext $n2nContext) {
		$securityFactory = new SecurityFactory($this->eiFieldCollection, 
				$this->getEiCommandCollection(), $this->eiModificatorCollection);
		return $securityFactory->createPrivilegedDefinition($n2nContext);
	}
	
	public function createEiMapping(EiFrame $eiFrame, EiEntry $eiEntry): EiMapping {
		$mappingFactory = new MappingFactory($this->eiFieldCollection, $this->eiModificatorCollection);
		return $mappingFactory->createEiMapping($eiFrame, $eiEntry);
	}
	
	public function createEiMappingCopy(EiFrame $eiFrame, EiEntry $eiEntry, EiMapping $from) {
		$mappingFactory = new MappingFactory($this->eiFieldCollection, $this->eiModificatorCollection);
		return $mappingFactory->createEiMapping($eiFrame, $eiEntry, $from);
	}
	
	
	public function getGuiDefinition(): GuiDefinition {
		if ($this->guiDefinition === null) {
			$guiFactory = new GuiFactory($this->eiFieldCollection, $this->eiModificatorCollection);
			$this->guiDefinition = $guiFactory->createGuiDefinition();
		}
	
		return $this->guiDefinition;
	}
	
	public function createEiEntryGui(EiuEntry $eiuEntry, int $viewMode, array $guiIdPaths) {
		$eiMask = $this->eiMask;
		if ($this->eiSpec === null) {
			$eiMask = $this->eiSpec->getEiMaskCollection()->getOrCreateDefault();
		}
		
		$guiFactory = new GuiFactory($this->getEiFieldCollection(), $this->getEiModificatorCollection());
		return $guiFactory->createEiEntryGui($eiMask, $eiuEntry, $viewMode, $guiIdPaths);
	}
	
	public function getDraftDefinition(): DraftDefinition {
		if ($this->draftDefinition !== null) {
			return $this->draftDefinition;
		}
		
		$eiThing = $this->eiMask;
		do {
			$id = $eiThing->getId();
		} while (($id === null || $eiThing->getEiEngine()->getEiFieldCollection()->isEmpty(true))
				&& null !== ($eiThing = $eiThing->getMaskedEiThing()));
		return $this->draftDefinition = (new DraftDefinitionFactory($this->eiMask->getEntityModel(), 
						$this->eiFieldCollection, $this->eiModificatorCollection))
				->create(DraftMetaInfo::buildTableName($eiThing));
	}

	/**
	 * @return rocket\spec\ei\manage\generic\GenericEiDefinition
	 */
	public function getGenericEiDefinition() {
		if ($this->genericEiDefinition !== null) {
			return $this->genericEiDefinition;
		}
		
		$this->genericEiDefinition = new GenericEiDefinition();
		$genericEiProperties = $this->genericEiDefinition->getGenericEiProperties();
		foreach ($this->eiFieldCollection as $eiField) {
			if ($eiField instanceof GenericEiField 
					&& $genericEiProperty = $eiField->getGenericEiProperty()) {
				ArgUtils::valTypeReturn($genericEiProperty, GenericEiProperty::class, $eiField, 
						'getGenericEiProperty', true);
				$genericEiProperties->offsetSet(EiFieldPath::from($eiField), $genericEiProperty);		
			}
		}
		return $this->genericEiDefinition;
	}
	
	public function getScalarEiDefinition() {
		if ($this->scalarEiDefinition !== null) {
			return $this->scalarEiDefinition;
		}
		
		$this->scalarEiDefinition = new ScalarEiDefinition();
		$scalarEiProperties = $this->scalarEiDefinition->getScalarEiProperties();
		foreach ($this->eiFieldCollection as $eiField) {
			if ($eiField instanceof ScalarEiField
					&& null !== ($scalarEiProperty = $eiField->getScalarEiProperty())) {
				ArgUtils::valTypeReturn($scalarEiProperty, ScalarEiProperty::class, $eiField, 'getScalarEiProperty', true);
				$scalarEiProperties->offsetSet(EiFieldPath::from($eiField), $scalarEiProperty);
			}
		}
		return $this->scalarEiDefinition;
	}
}
