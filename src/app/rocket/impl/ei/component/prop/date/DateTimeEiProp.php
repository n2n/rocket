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
namespace rocket\impl\ei\component\prop\date;

use n2n\impl\persistence\orm\property\DateTimeEntityProperty;
use n2n\l10n\L10nUtils;
use n2n\l10n\DateTimeFormat;
use n2n\l10n\N2nLocale;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\persistence\orm\property\EntityProperty;
use rocket\spec\ei\component\prop\SortableEiProp;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\critmod\sort\impl\SimpleSortField;
use rocket\spec\ei\manage\control\IconType;
use rocket\impl\ei\component\prop\adapter\DraftableEiPropAdapter;
use n2n\reflection\ArgUtils;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use rocket\impl\ei\component\prop\date\conf\DateTimeEiPropConfigurator;
use rocket\spec\ei\manage\draft\stmt\PersistDraftStmtBuilder;
use rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use rocket\spec\ei\manage\draft\SimpleDraftValueSelection;
use n2n\persistence\meta\OrmDialectConfig;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\draft\DraftManager;
use rocket\spec\ei\manage\draft\DraftValueSelection;
use rocket\spec\ei\manage\draft\PersistDraftAction;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\EiFrame;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\prop\indepenent\EiPropConfigurator;
use n2nutil\jquery\datepicker\mag\DateTimePickerMag;
use n2n\impl\web\ui\view\html\HtmlElement;
use rocket\spec\ei\manage\critmod\sort\SortField;

class DateTimeEiProp extends DraftableEiPropAdapter implements SortableEiProp {
	private $dateStyle = DateTimeFormat::STYLE_MEDIUM;
	private $timeStyle = DateTimeFormat::STYLE_SHORT;

	public function createEiPropConfigurator(): EiPropConfigurator {
		return new DateTimeEiPropConfigurator($this);
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof DateTimeEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('DateTime', 
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
		
	public function getDateStyle() {
		return $this->dateStyle;
	}
	
	public function setDateStyle($dateStyle) {
		ArgUtils::valEnum($dateStyle, DateTimeFormat::getStyles());
		$this->dateStyle = $dateStyle;
	}
	
	public function getTimeStyle() {
		return $this->timeStyle;
	}
	
	public function setTimeStyle($timeStyle) {
		ArgUtils::valEnum($timeStyle, DateTimeFormat::getStyles());
		$this->timeStyle = $timeStyle;
	}
	
	public function createOutputUiComponent(HtmlView $view, Eiu $eiu)  {
		return $view->getHtmlBuilder()->getL10nDateTime($eiu->field()->getValue(EiPropPath::from($this)), 
				$this->getDateStyle(), $this->getTimeStyle());
	}
	
	public function createMag(Eiu $eiu): Mag {
		$iconElem = new HtmlElement('i', array('class' => IconType::ICON_CALENDAR), '');
		
		return new DateTimePickerMag($this->getLabelLstr(), $iconElem, $this->getDateStyle(), $this->getTimeStyle(), null, null, 
				$this->isMandatory($eiu), array('placeholder' => $this->getLabelLstr(),
						'class' => 'form-control rocket-date-picker'));
	}


	public function isStringRepresentable(): bool {
		return true;
	}
	
    public function buildIdentityString(EiObject $eiObject, N2nLocale $n2nLocale): ?string {
        if (null !== ($dateTime = $this->read($eiObject))) {
            return L10nUtils::formatDateTime($dateTime, $n2nLocale, $this->getDateStyle(), $this->getTimeStyle());
        }

        return null;
    } 
	
	public function createDraftValueSelection(FetchDraftStmtBuilder $selectDraftStmtBuilder, DraftManager $dm, 
			N2nContext $n2nContext): DraftValueSelection {
		return new DateTimeDraftValueSelection($selectDraftStmtBuilder->requestColumn(EiPropPath::from($this)),
				$selectDraftStmtBuilder->getPdo()->getMetaData()->getDialect()->getOrmDialectConfig());
	}
	
	public function supplyPersistDraftStmtBuilder($value, $oldValue, PersistDraftStmtBuilder $persistDraftStmtBuilder,
			PersistDraftAction $persistDraftAction) {
		ArgUtils::valType($value, 'DateTime', true);
				
		$persistDraftStmtBuilder->registerColumnRawValue(EiPropPath::from($this), 
				$persistDraftStmtBuilder->getPdo()->getMetaData()->getDialect()->getOrmDialectConfig()
						->buildDateTimeRawValue($value));
	}
	
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\prop\SortableEiProp::createSortCriteriaConstraint()
	 */
	public function buildManagedSortField(EiFrame $eiFrame): ?SortField {
		return $this->buildSortField($eiFrame->getN2nContext());
	}

	public function buildSortField(N2nContext $n2nContext): ?SortField {
		return new SimpleSortField(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
}

class DateTimeDraftValueSelection extends SimpleDraftValueSelection {
	private $ormDialectConfig;
	
	public function __construct($columnAlias, OrmDialectConfig $ormDialectConfig) {
		parent::__construct($columnAlias);
		$this->ormDialectConfig = $ormDialectConfig;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\draft\DraftValueSelection::buildDraftValue()
	 */
	public function buildDraftValue() {
		return $this->ormDialectConfig->parseDateTime($this->rawValue);
	}
}