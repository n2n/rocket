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
namespace rocket\ei\manage\idname;

use n2n\l10n\N2nLocale;
use rocket\ei\EiPropPath;
use rocket\ei\manage\EiObject;
use n2n\l10n\Lstr;
use n2n\core\container\N2nContext;

class IdNameDefinition {
	private $identityStringPattern;
	private $labelLstr;
	private $idNameProps = array();
	private $idNamePropForks = array();
	private $eiPropPaths = array();
	
	function __construct(Lstr $labelLstr) {
		$this->labelLstr = $labelLstr;
	}
	
	/**
	 * @param string|null $identityStringPattern
	 */
	public function setIdentityStringPattern(?string $identityStringPattern) {
		$this->identityStringPattern = $identityStringPattern;
	}
	
	/**
	 * @return string|null
	 */
	public function getIdentityStringPattern() {
		return $this->identityStringPattern;
	}
	
	/**
	 * @return IdNameProp[]
	 */
	public function getIdNameProps() {
		return $this->idNameProps;
	}
	
	/**
	 * @return IdNamePropFork[]
	 */
	public function getIdNamePropForks() {
		return $this->idNamePropForks;
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	private function createDefaultIdentityString(EiObject $eiObject, N2nContext $n2nContext, N2nLocale $n2nLocale) {
		$eiType = $eiObject->getEiEntityObj()->getEiType();
		
		$idPatternPart = null;
		$namePatternPart = null;
		
		foreach ($this->getStringRepresentableIdNameProps() as $eiPropPathStr => $idNameProp) {
			if ($eiPropPathStr == $eiType->getEntityModel()->getIdDef()->getPropertyName()) {
				$idPatternPart = SummarizedStringBuilder::createPlaceholder($eiPropPathStr);
			} else {
				$namePatternPart = SummarizedStringBuilder::createPlaceholder($eiPropPathStr);
			}
			
			if ($namePatternPart !== null) break;
		}
		
		if ($idPatternPart === null) {
			$idPatternPart = $eiObject->getEiEntityObj()->hasId() ?
			$eiType->idToPid($eiObject->getEiEntityObj()->getId()) : 'new';
		}
		
		if ($namePatternPart === null) {
			$namePatternPart = $this->labelLstr->t($n2nLocale);
		}
		
		return $this->createIdentityStringFromPattern($namePatternPart . ' #' . $idPatternPart, 
				$n2nContext, $eiObject, $n2nLocale);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(EiObject $eiObject, N2nContext $n2nContext, N2nLocale $n2nLocale) {
		if ($this->identityStringPattern === null) {
			return $this->createDefaultIdentityString($eiObject, $n2nContext, $n2nLocale);
		}
		
		return $this->createIdentityStringFromPattern($this->identityStringPattern, $n2nContext, $eiObject, $n2nLocale);
	}
	
	/**
	 * @param $entity
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityStringFromPattern(string $identityStringPattern, N2nContext $n2nContext, 
			EiObject $eiObject, N2nLocale $n2nLocale): string {
		$builder = new SummarizedStringBuilder($identityStringPattern, $n2nContext, $n2nLocale);
		$builder->replaceFields(array(), $this, $eiObject);
		return $builder->__toString();
	}
	
	public function getStringRepresentableIdNameProps() {
		return $this->filterStringRepresentableIdNameProps($this, array());
	}
	
	private function filterStringRepresentableIdNameProps(IdNameDefinition $idNameDefinition, array $baseIds) {
		$idNameProps = array();
		
		foreach ($idNameDefinition->getIdNameProps() as $id => $idNameProp) {
			if (!$idNameProp->isStringRepresentable()) continue;
			
			$ids = $baseIds;
			$ids[] = EiPropPath::create($id);
			$idNameProps[(string) new IdNamePath($ids)] = $idNameProp;
		}
		
		foreach ($idNameDefinition->getIdNamePropForks() as $id => $idNamePropFork) {
			$forkedIdNameDefinition = $idNamePropFork->getForkedIdNameDefinition();
			
			if ($forkedIdNameDefinition === null) continue;
			
			$ids = $baseIds;
			$ids[] = EiPropPath::create($id);
			$idNameProps = array_merge($idNameProps, 
					$this->filterStringRepresentableIdNameProps($forkedIdNameDefinition, $ids));
		}
		
		return $idNameProps;
	}
	
	
}