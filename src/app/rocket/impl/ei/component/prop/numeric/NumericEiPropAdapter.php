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
use rocket\ei\manage\critmod\filter\impl\prop\StringFilterProp;
use rocket\ei\component\prop\SortableEiProp;
use rocket\ei\component\prop\FilterableEiProp;
use n2n\l10n\N2nLocale;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\ei\manage\critmod\sort\impl\SimpleSortField;

use rocket\impl\ei\component\prop\adapter\DraftableEiPropAdapter;
use rocket\impl\ei\component\prop\numeric\conf\NumericEiPropConfigurator;
use n2n\reflection\ArgUtils;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\frame\EiFrame;
use n2n\core\container\N2nContext;
use rocket\ei\EiPropPath;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\critmod\sort\SortField;
use rocket\ei\util\model\Eiu;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\manage\critmod\quick\impl\model\LikeQuickSearchField;
use rocket\ei\manage\critmod\filter\FilterProp;

abstract class NumericEiPropAdapter extends DraftableEiPropAdapter 
		implements FilterableEiProp, SortableEiProp, QuickSearchableEiProp {
	
	protected $minValue = null;
	protected $maxValue = null;
	
	public function getMinValue() {
		return $this->minValue;
	}
	
	public function setMinValue($minValue) {
		$this->minValue = $minValue;
	}
	
	public function getMaxValue() {
		return $this->maxValue;
	}
	
	public function setMaxValue($maxValue) {
		$this->maxValue = $maxValue;
	}

	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		ArgUtils::assertTrue($propertyAccessProxy !== null);
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('scalar',
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}

	public function createEiPropConfigurator(): EiPropConfigurator {
		return new NumericEiPropConfigurator($this);
	}
	
	public function createOutputUiComponent(HtmlView $view, Eiu $eiu)  {
		$html = $view->getHtmlBuilder();
		return $html->getEsc($eiu->field()->getValue(EiPropPath::from($this)));
	}
	
// 	public function createPreviewUiComponent(EiFrame $eiFrame = null, HtmlView $view, $value) {
// 		return $view->getHtmlBuilder()->getEsc($value);
// 	}
	
	public function buildFilterProp(Eiu $eiu): ?FilterProp {
		return new StringFilterProp(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
	
	public function buildSecurityFilterProp(N2nContext $n2nContext) {
		return null;
	}
	
	public function buildManagedSortField(EiFrame $eiFrame): ?SortField {
		return $this->buildSortField($eiFrame->getN2nContext());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\SortableEiProp::createGlobalSortField()
	 * @return SortField
	 */
	public function buildSortField(N2nContext $n2nContext): ?SortField {
		return new SimpleSortField(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
	
	public function buildQuickSearchField(EiFrame $eiFrame) {
		return new LikeQuickSearchField(CrIt::p($this->getEntityProperty()));
	}

// 	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
// 			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
// 		return $view->getFormHtmlBuilder()->getInputField($propertyPath, 
// 				array('class' => 'rocket-preview-inpage-component'));
// 	}

	public function isStringRepresentable(): bool {
		return true;
	}
	
	public function buildIdentityString(EiObject $eiObject, N2nLocale $n2nLocale): ?string {
		return $this->read($eiObject);
	}
}
