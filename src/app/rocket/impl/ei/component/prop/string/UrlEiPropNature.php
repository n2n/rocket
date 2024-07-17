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

use rocket\op\ei\util\Eiu;
use n2n\util\uri\Url;
use n2n\impl\persistence\orm\property\UrlEntityProperty;
use rocket\op\ei\manage\critmod\quick\QuickSearchProp;
use rocket\op\ei\util\factory\EifField;
use n2n\validation\validator\impl\Validators;
use rocket\ui\si\content\impl\SiFields;
use rocket\op\ei\util\factory\EifGuiField;
use n2n\util\type\CastUtils;
use rocket\ui\si\content\impl\StringInSiField;
use rocket\ui\si\control\SiNavPoint;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\util\type\TypeConstraints;
use n2n\util\type\ArgUtils;

class UrlEiPropNature extends AlphanumericEiPropNature {

	function __construct(PropertyAccessProxy $propertyAccessProxy) {
		parent::__construct($propertyAccessProxy->createRestricted(TypeConstraints::type([Url::class, 'string', 'null'])));
	}



	private $autoScheme = null;
	private $allowedSchemes = null;
	private $relativeAllowed = false;
	private $lytebox = false;


	public function setAllowedSchemes(?array $allowedSchemes) {
		ArgUtils::valArray($allowedSchemes, 'string', true);
		$this->allowedSchemes = $allowedSchemes;
	}

	/**
	 * @return string[]|null
	 */
	public function getAllowedSchemes() {
		return $this->allowedSchemes;
	}

	public function isRelativeAllowed(): bool {
		return $this->relativeAllowed;
	}

	public function setRelativeAllowed(bool $relativeAllowed) {
		$this->relativeAllowed = $relativeAllowed;
	}

	public function setAutoScheme(string $autoScheme = null) {
		$this->autoScheme = $autoScheme;
	}

	public function getAutoScheme() {
		return $this->autoScheme;
	}

	public function isLytebox(): bool {
		return $this->lytebox;
	}

	public function setLytebox(bool $lytebox) {
		$this->lytebox = $lytebox;
	}

	public function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		if ($this->entityProperty instanceof UrlEntityProperty) {
			return null;
		}
		
		return parent::buildQuickSearchProp($eiu);
	}

	function createEifField(Eiu $eiu): EifField {
		return parent::createEifField($eiu)
				->setReadMapper(function ($value) { return $this->readMap($value); })
				->setWriteMapper(function ($value) use ($eiu) { return $this->writeMap($eiu, $value); })
				->val(Validators::url(!$this->isRelativeAllowed(), $this->getAllowedSchemes()));
	}
	
	/**
	 * @param string|Url|null $value
	 * @return string|Url|null
	 */
	private function readMap($value) {
		try {
			return Url::build($value, true);
		} catch (\InvalidArgumentException $e) {
			return null;
		}
	}
	
	/**
	 * @param Eiu $eiu
	 * @param string|Url|null $value
	 * @return string|\n2n\util\uri\Url
	 */
	private function writeMap(Eiu $eiu, $value) {
		if ($value instanceof Url
				&& $this->getPropertyAccessProxy()->getConstraint()->getTypeName() != Url::class) {
			return (string) $value;
		}
		
		return $value;
	}
	
	function buildInGuiField(Eiu $eiu): ?BackableGuiField {
		$siField = SiFields::stringIn($eiu->field()->getValue())
				->setMandatory($this->isMandatory())
				->setMinlength($this->getMinlength())
				->setMaxlength($this->getMaxlength())
				->setPrefixAddons($this->getPrefixSiCrumbGroups())
				->setSuffixAddons($this->getSuffixSiCrumbGroups())
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
		
		return $eiu->factory()->newGuiField($siField)
				->setSaver(function () use ($eiu, $siField) {
					CastUtils::assertTrue($siField instanceof StringInSiField);
					$eiu->field()->setValue($this->mapSiValue($siField->getValue()));
				});
				
// 		$allowedSchemes = $this->urlConfig->getAllowedSchemes();
// 		if (!empty($allowedSchemes)) {
// 			$mag->setAllowedSchemes($allowedSchemes);
// 		}
		
// 		$mag->setRelativeAllowed($this->urlConfig->isRelativeAllowed());
// 		$mag->setAutoScheme($this->urlConfig->getAutoScheme());
// 		$mag->setInputAttrs(array('placeholder' => $this->getLabelLstr(), 'class' => 'form-control'));
// 		$mag->setAttrs(array('class' => 'rocket-block'));

		
	}
	
	private function mapSiValue($value) {
		if ($value === null) {
			return null;
		}
		
		$url = Url::create($value, true);
		
		$autoScheme = $this->getAutoScheme();
		if ($autoScheme !== null && !$url->hasScheme()) {
			$url = $url->chScheme($autoScheme);
		}
		
		return $url;
	}
	
	public function buildOutGuiField(Eiu $eiu): ?BackableGuiField  {
		$value = $eiu->field()->getValue();
		if ($value === null) {
			return $eiu->factory()->newGuiField(SiFields::stringOut(null)
					->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs()));
		}
		
		$label = $this->buildLabel(Url::create($value, true), $eiu->guiEntry()->isBulky());
		return $eiu->factory()->newGuiField(
				SiFields::linkOut(SiNavPoint::href(Url::create($value, true)), $label)
						->setLytebox($this->isLytebox())
						->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs()));
	}
	

	private function buildLabel(Url $url, bool $isBulkyMode) {
		if ($isBulkyMode) return (string) $url;

		$label = (string) $url->getAuthority();

		$pathParts = $url->getPath()->getPathParts();
		if (!empty($pathParts)) {
			$label .= '/.../' . array_pop($pathParts);
		}

		$query = $url->getQuery();
		if (!$query->isEmpty()) {
			$queryArr = $query->toArray();
			$label .= '?' . key($queryArr) . '=...';
		}

		return $label;
	}
}
