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


use rocket\op\ei\util\filter\prop\StringFilterProp;
 

use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropNatureAdapter;
use n2n\util\type\ArgUtils;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\TypeConstraint;
use n2n\core\container\N2nContext;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\op\ei\manage\critmod\sort\SortProp;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\critmod\quick\impl\LikeQuickSearchProp;
use rocket\op\ei\manage\critmod\filter\FilterProp;
use rocket\op\ei\manage\critmod\sort\impl\SimpleSortProp;
use rocket\op\ei\manage\critmod\quick\QuickSearchProp;
use rocket\impl\ei\component\prop\numeric\conf\NumericAdapter;
use rocket\op\ei\manage\idname\IdNameProp;
use rocket\impl\ei\component\prop\adapter\config\QuickSearchConfigTrait;
use rocket\op\ei\util\factory\EifGuiField;
use n2n\l10n\L10nUtils;
use rocket\si\content\impl\SiFields;
use rocket\impl\ei\component\prop\meta\AddonAdapter;
use rocket\impl\ei\component\prop\meta\AddonEiPropNature;

abstract class NumericEiPropNatureAdapter extends DraftablePropertyEiPropNatureAdapter
		implements AddonEiPropNature {
	use NumericAdapter, AddonAdapter, QuickSearchConfigTrait;

//
//	function setEntityProperty(?EntityProperty $entityProperty) {
//		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty);
//		$this->entityProperty = $entityProperty;
//	}
//
//	function setPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
//		ArgUtils::assertTrue($propertyAccessProxy !== null);
//		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('scalar',
//				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
//		$this->propertyAccessProxy = $propertyAccessProxy;
//	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\numeric\NumericEiPropNatureAdapter::createOutSiField()
	 */
	function createOutEifGuiField(Eiu $eiu): EifGuiField {
		return $eiu->factory()->newGuiField(SiFields
				::stringOut(L10nUtils::formatNumber($eiu->field()->getValue(), $eiu->getN2nLocale()))
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs()));
	}
	
// 	function createPreviewUiComponent(EiFrame $eiFrame = null, HtmlView $view, $value) {
// 		return $view->getHtmlBuilder()->getEsc($value);
// 	}
	
	function buildFilterProp(Eiu $eiu): ?FilterProp {
		return new StringFilterProp(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}

	function buildSortProp(Eiu $eiu): ?SortProp {
		return new SimpleSortProp(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\prop\QuickSearchableEiProp::buildQuickSearchProp()
	 */
	function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		if ($this->isQuickSearchable()) {
			return new LikeQuickSearchProp(CrIt::p($this->getEntityProperty()));
		}
		
		return null;
	}

// 	function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
// 			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
// 		return $view->getFormHtmlBuilder()->getInputField($propertyPath, 
// 				array('class' => 'rocket-preview-inpage-component'));
// 	}
	
	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			return $eiu->object()->readNativeValue($eiu->prop()->getEiProp());
		})->toIdNameProp();
	}
}
