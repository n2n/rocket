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

use n2n\l10n\L10nUtils;
use n2n\core\container\N2nContext;
use rocket\op\ei\manage\critmod\sort\impl\SimpleSortProp;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropNatureAdapter;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\draft\stmt\PersistDraftStmtBuilder;
use rocket\op\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use rocket\op\ei\manage\draft\SimpleDraftValueSelection;
use n2n\persistence\meta\OrmDialectConfig;
use rocket\op\ei\manage\draft\DraftManager;
use rocket\op\ei\manage\draft\DraftValueSelection;
use rocket\op\ei\manage\draft\PersistDraftAction;
use rocket\op\ei\EiPropPath;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\op\ei\util\Eiu;
use n2nutil\jquery\datepicker\mag\DateTimePickerMag;
use rocket\op\ei\manage\critmod\sort\SortProp;
use rocket\ui\si\content\SiField;
use rocket\op\ei\manage\idname\IdNameProp;
use rocket\op\ei\util\factory\EifGuiField;
use rocket\ui\si\content\impl\SiFields;
use n2n\l10n\DateTimeFormat;
use n2n\util\type\TypeConstraints;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\ui\gui\field\impl\GuiFields;
use rocket\ui\gui\field\BackableGuiField;

class DateTimeEiPropNature extends DraftablePropertyEiPropNatureAdapter  {

	private $dateStyle = DateTimeFormat::STYLE_MEDIUM;
	private $timeStyle = DateTimeFormat::STYLE_NONE;

	function __construct(PropertyAccessProxy $propertyAccessProxy) {
		parent::__construct($propertyAccessProxy->createRestricted(TypeConstraints::namedType(\DateTime::class, true)));
	}

	function getDateStyle() {
		return $this->dateStyle;
	}

	function setDateStyle($dateStyle) {
		ArgUtils::valEnum($dateStyle, DateTimeFormat::getStyles());
		$this->dateStyle = $dateStyle;
	}

	function getTimeStyle() {
		return $this->timeStyle;
	}

	function setTimeStyle($timeStyle) {
		ArgUtils::valEnum($timeStyle, DateTimeFormat::getStyles());
		$this->timeStyle = $timeStyle;
	}
	
	public function buildOutGuiField(Eiu $eiu): ?BackableGuiField  {
		$dateTime = $eiu->field()->getValue();

		return GuiFields::out(SiFields::stringOut($dateTime === null ? ''
				: L10nUtils::formatDateTime($dateTime, $eiu->getN2nLocale(), $this->getDateStyle(), $this->getTimeStyle())));
	}
	
	public function buildInGuiField(Eiu $eiu): ?BackableGuiField {
		return GuiFields::dateTimeIn($this->isMandatory(),
						$this->getDateStyle() !== DateTimeFormat::STYLE_NONE,
						$this->getTimeStyle() !== DateTimeFormat::STYLE_NONE)
				->setValue($eiu->field()->getValue());
	}
	
	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			if (null !== ($dateTime = $eiu->object()->readNativeValue($eiu->prop()->getEiProp()))) {
				return L10nUtils::formatDateTime($dateTime, $eiu->getN2nLocale(), $this->getDateStyle(), $this->getTimeStyle());
			}
			
			return null;
		})->toIdNameProp();
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

	public function buildSortProp(Eiu $eiu): ?SortProp {
		return new SimpleSortProp(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
	public function saveSiField(SiField $siField, Eiu $eiu) {
	}

}

class DateTimeDraftValueSelection extends SimpleDraftValueSelection {
	private $ormDialectConfig;
	
	public function __construct($columnAlias, OrmDialectConfig $ormDialectConfig) {
		parent::__construct($columnAlias);
		$this->ormDialectConfig = $ormDialectConfig;
	}
	/* (non-PHPdoc)
	 * @see \rocket\op\ei\manage\draft\DraftValueSelection::buildDraftValue()
	 */
	public function buildDraftValue() {
		return $this->ormDialectConfig->parseDateTime($this->rawValue);
	}
}