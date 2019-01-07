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
namespace rocket\impl\ei\component\prop\string;

use n2n\l10n\N2nLocale;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use n2n\l10n\DynamicTextCollection;
use rocket\impl\ei\component\prop\string\conf\PathPartEiPropConfigurator;
use n2n\web\dispatch\mag\Mag;
use n2n\util\type\ArgUtils;
use rocket\ei\EiPropPath;
use rocket\ei\util\Eiu;
use rocket\ei\manage\generic\GenericEiProperty;
use rocket\ei\manage\generic\ScalarEiProperty;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\manage\gui\ViewMode;

class PathPartEiProp extends AlphanumericEiProp  {
	const URL_COUNT_SEPERATOR = '-';
	
	private $nullAllowed = false;
	private $baseScalarEiProperty;
	private $uniquePerGenericEiProperty;
	private $critical = false;
	private $criticalMessage;
	private $criticalMessageCodeDtc;
	
	private $urlEiCommand;
	
	public function __construct() {
		$this->getDisplayConfig()->setDefaultDisplayedViewModes(ViewMode::BULKY_EDIT | ViewMode::COMPACT_READ);
		$this->entityPropertyRequired = true;
		$this->getEditConfig()->setMandatory(false);
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\impl\ei\component\prop\EditablePropertyEiPropAdapter::createEiConfigurator()
	 */
	public function createEiPropConfigurator(): EiPropConfigurator {
		return new PathPartEiPropConfigurator($this);
	}
	
	public function getTypeName(): string {
		return 'Path Part';
	}
	
	public function isNullAllowed(): bool {
		return $this->nullAllowed;
	}

	public function setNullAllowed(bool $nullAllowed) {
		$this->nullAllowed = $nullAllowed;
	}

	public function getBaseScalarEiProperty() {
		return $this->baseScalarEiProperty;
	}

	public function setBaseScalarEiProperty(ScalarEiProperty $baseScalarEiProperty = null) {
		$this->baseScalarEiProperty = $baseScalarEiProperty;
	}

	/**
	 * @return \rocket\ei\manage\generic\GenericEiProperty
	 */
	public function getUniquePerGenericEiProperty() {
		return $this->uniquePerGenericEiProperty;
	}

	public function setUniquePerGenericEiProperty(GenericEiProperty $uniquePerCriteriaProperty = null) {
		$this->uniquePerGenericEiProperty = $uniquePerCriteriaProperty;
	}

	public function isCritical(): bool {
		return $this->critical;
	}

	public function setCritical(bool $critical) {
		$this->critical = $critical;
	}

	public function getCriticalMessage() {
		return $this->criticalMessage;
	}

	public function setCriticalMessage(string $criticalMessage = null) {
		$this->criticalMessage = $criticalMessage;
	}

// 	public function getUrlEiCommand() {
// 		return $this->urlEiCommand;
// 	}

// 	public function setUrlEiCommand($urlEiCommand) {
// 		$this->urlEiCommand = $urlEiCommand;
// 	}

	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty);
		
		parent::setEntityProperty($entityProperty);
	}
	
	public function createUiComponent(HtmlView $view, Eiu $eiu)  {
		return $view->getHtmlBuilder()->getEsc($eiu->field()->getValue(EiPropPath::from($this)));
	}

	
	private function buildMagInputAttrs(Eiu $eiu): array {
		$attrs = array('placeholder' => $this->getLabelLstr(), 'class' => 'form-control');
		
		if ($eiu->entry()->isNew() || $eiu->entry()->isDraft() || !$this->critical) return $attrs;
	
		$attrs['class'] = 'rocket-critical-input';
		
		if (null !== $this->criticalMessage) {
			$dtc = new DynamicTextCollection('rocket', $eiu->getRequest()->getN2nLocale());
			$attrs['data-confirm-message'] = $this->criticalMessage;
			$attrs['data-edit-label'] =  $dtc->translate('common_edit_label');
			$attrs['data-cancel-label'] =  $dtc->translate('common_cancel_label');
		}
		
		return $attrs;
	}
	
	public function createMag(Eiu $eiu): Mag {
		$attrs = $this->buildMagInputAttrs($eiu);
		
		return new StringMag($this->getLabelLstr(), null,
				$this->isMandatory($eiu), $this->getMaxlength(), false, null, $attrs);
	}
	
	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): string {
		return $eiu->object()->readNativValue($this);
	}
}
