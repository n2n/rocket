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
namespace rocket\impl\ei\component\prop\numeric;

use rocket\ei\component\prop\QuickSearchableEiProp;
use rocket\ei\util\filter\prop\StringFilterProp;
use rocket\ei\component\prop\SortableEiProp;
use rocket\ei\component\prop\FilterableEiProp;
use n2n\l10n\N2nLocale;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use n2n\util\type\ArgUtils;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\TypeConstraint;
use n2n\core\container\N2nContext;
use rocket\ei\EiPropPath;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\critmod\sort\SortProp;
use rocket\ei\util\Eiu;
use rocket\ei\manage\critmod\quick\impl\LikeQuickSearchProp;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\manage\critmod\sort\impl\SimpleSortProp;
use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\prop\numeric\conf\NumericConfig;

abstract class NumericEiPropAdapter extends DraftablePropertyEiPropAdapter 
		implements FilterableEiProp, SortableEiProp, QuickSearchableEiProp {
    
	private $numericConfig;
		    
    function __construct() {
        parent::__construct();
        
        $this->numericConfig = new NumericConfig();
    }

    /**
     * @return \rocket\impl\ei\component\prop\numeric\conf\NumericConfig
     */
    function getNumericConfig() {
        return $this->numericConfig;
    }
    
	function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		ArgUtils::assertTrue($propertyAccessProxy !== null);
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('scalar',
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}

	function adaptConfigurator(AdaptableEiPropConfigurator $configurator) {
	    $configurator->addAdaption($this->numericConfig);
	}
	
	function createOutSiField(Eiu $eiu): SiField  {
		$html = $view->getHtmlBuilder();
		return $html->getEsc($eiu->field()->getValue(EiPropPath::from($this)));
	}
	
// 	function createPreviewUiComponent(EiFrame $eiFrame = null, HtmlView $view, $value) {
// 		return $view->getHtmlBuilder()->getEsc($value);
// 	}
	
	function buildFilterProp(Eiu $eiu): ?FilterProp {
		return new StringFilterProp(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
	
	function buildSecurityFilterProp(N2nContext $n2nContext) {
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\SortableEiProp::createGlobalSortProp()
	 * @return SortProp
	 */
	function buildSortProp(Eiu $eiu): ?SortProp {
		return new SimpleSortProp(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\QuickSearchableEiProp::buildQuickSearchProp()
	 */
	function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		return new LikeQuickSearchProp(CrIt::p($this->getEntityProperty()));
	}

// 	function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
// 			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
// 		return $view->getFormHtmlBuilder()->getInputField($propertyPath, 
// 				array('class' => 'rocket-preview-inpage-component'));
// 	}

	function isStringRepresentable(): bool {
		return true;
	}
	
	function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		return $eiu->object()->readNativValue($this);
	}
}
