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
namespace rocket\impl\ei\component\prop\bool;

use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\ei\component\prop\SortableEiProp;
use rocket\ei\component\prop\FilterableEiProp;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\ei\manage\EiFrame;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\core\container\N2nContext;
use rocket\ei\manage\critmod\sort\impl\SimpleSortField;

use rocket\impl\ei\component\prop\adapter\DraftableEiPropAdapter;
use n2n\reflection\ArgUtils;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\web\dispatch\mag\Mag;
use rocket\ei\util\model\Eiu;
use rocket\ei\manage\critmod\filter\impl\field\BoolFilterField;
use rocket\ei\manage\EiObject;
use rocket\impl\ei\component\prop\bool\conf\BooleanEiPropConfigurator;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\manage\critmod\filter\FilterField;
use rocket\ei\manage\critmod\sort\SortField;
use rocket\ei\manage\gui\GuiIdPath;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;

class BooleanEiProp extends DraftableEiPropAdapter implements FilterableEiProp, SortableEiProp {

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\DraftableEiPropAdapter::createEiPropConfigurator()
	 */
	public function createEiPropConfigurator(): EiPropConfigurator {
		return new BooleanEiPropConfigurator($this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\EntityPropertyEiPropAdapter::setEntityProperty()
	 */
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty || $entityProperty === null);
		$this->entityProperty = $entityProperty;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\ObjectPropertyEiPropAdapter::setObjectPropertyAccessProxy()
	 */
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		if ($propertyAccessProxy === null) {
			return;
		}
		
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('scalar',
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyEditableEiPropAdapter::isMandatory()
	 */
	public function isMandatory(Eiu $eiu): bool {
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\DraftableEiPropAdapter::read()
	 */
	public function read(EiObject $eiObject) {
		return (bool) parent::read($eiObject);
	}
	
	private $onAssociatedGuiIdPaths = array();
	private $offAssociatedGuiIdPaths = array();
	
	/**
	 * @param GuiIdPath[] $onAssociatedGuiIdPaths
	 */
	public function setOnAssociatedGuiIdPaths(array $onAssociatedGuiIdPaths) {
		ArgUtils::valArray($onAssociatedGuiIdPaths, GuiIdPath::class);
		$this->onAssociatedGuiIdPaths = $onAssociatedGuiIdPaths;
	}
	
	/**
	 * @return GuiIdPath[]
	 */
	public function getOnAssociatedGuiIdPaths() {
		return $this->onAssociatedGuiIdPaths;
	}
	
	/**
	 * @param GuiIdPath[] $offAssociatedGuiIdPaths
	 */
	public function setOffAssociatedGuiIdPaths(array $offAssociatedGuiIdPaths) {
		ArgUtils::valArray($offAssociatedGuiIdPaths, GuiIdPath::class);
		$this->offAssociatedGuiIdPaths = $offAssociatedGuiIdPaths;
	}
	
	/**
	 * @return GuiIdPath[]
	 */
	public function getOffAssociatedGuiIdPaths() {
		return $this->offAssociatedGuiIdPaths;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\StatelessDisplayable::createOutputUiComponent()
	 */
	public function createOutputUiComponent(HtmlView $view, Eiu $eiu)  {
		$value = $this->getObjectPropertyAccessProxy()->getValue(
				$eiu->entry()->getEiEntry()->getEiObject()->getLiveObject());
		if ($value) {
			return new HtmlElement('i', array('class' => 'fa fa-check'), '');
		}
		return new HtmlElement('i', array('class' => 'fa fa-check-empty'), '');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\StatelessEditable::createMag()
	 */
	public function createMag(Eiu $eiu): Mag {
		if (empty($this->onAssociatedGuiIdPaths) && empty($this->offAssociatedGuiIdPaths)) {
			return new BoolMag($this->getLabelLstr(), true);
		}
		
		if ($eiu->entryGui()->isReady()) {
			throw new \Exception();
		}
		
		$enablerMag = new TogglerMag($this->getLabelLstr(), true);
		
		$that = $this;
		$eiu->entryGui()->whenReady(function () use ($eiu, $enablerMag, $that) {
			$onMagWrappers = array();
			foreach ($that->getOnAssociatedGuiIdPaths() as $guiIdPath) {
				$magWrapper = $eiu->entryGui()->getMagWrapper($guiIdPath, false);
				if ($magWrapper === null) continue;
				
				$onMagWrappers[] = $magWrapper;
			}
			$enablerMag->setOnAssociatedMagWrappers($onMagWrappers);

			$offMagWrappers = array();
			foreach ($that->getOffAssociatedGuiIdPaths() as $guiIdPath) {
				$magWrapper = $eiu->entryGui()->getMagWrapper($guiIdPath, false);
				if ($magWrapper === null) continue;
				
				$offMagWrappers[] = $magWrapper;
			}
			
			$enablerMag->setOffAssociatedMagWrappers($offMagWrappers);
		});
			
		return $enablerMag;
	}
	
	public function buildEiField(Eiu $eiu) {
		$that = $this;
		$eiu->entry()->onValidate(function () use ($eiu, $that) {
			$activeGuiIdPaths = array();
			$notactiveGuiIdPaths = array();
			
			if ($eiu->field()->getValue()) {
				$activeGuiIdPaths = $this->getOnAssociatedGuiIdPaths();
				$notactiveGuiIdPaths = $this->getOffAssociatedGuiIdPaths();
			} else {
				$activeGuiIdPaths = $this->getOffAssociatedGuiIdPaths();
				$notactiveGuiIdPaths = $this->getOnAssociatedGuiIdPaths();
			}
			
			foreach ($notactiveGuiIdPaths as $key => $guiIdPath) {
				if (null !== ($eiFieldWrapper = $eiu->entry()->getEiFieldWrapperByGuiIdPath($guiIdPath))) {
					$eiFieldWrapper->setIgnored(true);
				}
			}
			
			foreach ($activeGuiIdPaths as $guiIdPath) {
				if (null !== ($eiFieldWrapper = $eiu->entry()->getEiFieldWrapperByGuiIdPath($guiIdPath))) {
					$eiFieldWrapper->setIgnored(false);
				}
			}
		});
			
		return parent::buildEiField($eiu);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FilterableEiProp::buildManagedFilterField()
	 */
	public function buildManagedFilterField(EiFrame $eiFrame): ?FilterField  {
		return $this->buildFilterField($eiFrame->getN2nContext());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FilterableEiProp::buildFilterField()
	 */
	public function buildFilterField(N2nContext $n2nContext): ?FilterField {
		return $this->buildEiEntryFilterField($n2nContext);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FilterableEiProp::buildEiEntryFilterField()
	 */
	public function buildEiEntryFilterField(N2nContext $n2nContext) {
		return new BoolFilterField(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\SortableEiProp::buildManagedSortField()
	 */
	public function buildManagedSortField(EiFrame $eiFrame): ?SortField {
		return $this->buildSortField($eiFrame->getN2nContext());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\SortableEiProp::buildSortField()
	 */
	public function buildSortField(N2nContext $n2nContext): ?SortField {
		if (null !== ($entityProperty = $this->getEntityProperty())) {
			return new SimpleSortField(CrIt::p($entityProperty), $this->getLabelLstr());
		}
		
		return null;
	}
}
