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
namespace rocket\ei\component\prop;

use rocket\ei\component\EiComponentCollection;
use rocket\ei\component\UnknownEiComponentException;
use rocket\ei\mask\EiMask;
use rocket\ei\EiPropPath;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\critmod\quick\QuickSearchDefinition;
use rocket\ei\util\Eiu;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\critmod\filter\FilterProp;
use n2n\core\container\N2nContext;
use rocket\ei\manage\critmod\sort\SortDefinition;
use rocket\ei\manage\critmod\sort\SortPropFork;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\generic\GenericEiDefinition;
use rocket\ei\manage\generic\ScalarEiDefinition;
use rocket\ei\manage\idname\IdNameDefinition;
use rocket\ei\manage\frame\EiForkLink;
use rocket\ei\EiException;
use n2n\util\type\TypeUtils;

class EiPropCollection extends EiComponentCollection {
	private array $eiPropPaths = array();

	/**
	 * @param EiMask $eiMask
	 */
	public function __construct(EiMask $eiMask) {
		parent::__construct('EiProp', EiPropNature::class);
		$this->setEiMask($eiMask);
	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @return EiProp
	 * @throws UnknownEiComponentException
	 */
	public function getByPath(EiPropPath $eiPropPath) {
		return $this->getElementByIdPath($eiPropPath);
	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @return EiProp[]
	 */
	public function getForkedByPath(EiPropPath $eiPropPath) {
		return $this->getElementsByForkIdPath($eiPropPath);
	}

	/**
	 * @param string $id
	 * @param EiPropNature $eiPropNature
	 * @param EiPropPath $forkEiPropPath
	 * @return EiProp
	 */
	public function add(?string $id, EiPropNature $eiPropNature, EiPropPath $forkEiPropPath = null) {
		$id = $this->makeId($id, $eiPropNature);

		$eiPropPath = null;
		if ($forkEiPropPath === null) {
			$eiPropPath = new EiPropPath([$id]);
		} else {
			$eiPropPath = $forkEiPropPath->ext($id);
		}

		$eiPropNature = new EiProp($eiPropPath, $eiPropNature, $this);

		$this->addEiComponent($eiPropPath, $eiPropNature);

		return $eiPropNature;
	}

	function changeOrder(array $eiPropPaths) {
		$this->changeEiComponentOrder($eiPropPaths);
	}

	/**
	 * @param bool $includeInherited
	 * @return EiPropNature[]
	 */
	function toArray(bool $includeInherited = true): array {
		return parent::toArray($includeInherited);
	}

	function createGenericEiDefinition(): GenericEiDefinition {
		$genericEiDefinition = new GenericEiDefinition();

		$genericEiPropertyMap = $genericEiDefinition->getMap();
		foreach ($this as $eiProp) {
			if (null !== ($genericEiProperty = $eiProp->getNature()->getGenericEiProperty())) {
				$genericEiPropertyMap->offsetSet($eiProp->getEiPropPath(), $genericEiProperty);
			}
		}

		return $genericEiDefinition;
	}

	function createScalarEiDefinition(): ScalarEiDefinition {
		$scalarEiDefinition = new ScalarEiDefinition();
		$scalarEiProperties = $scalarEiDefinition->getMap();
		foreach ($this as $eiProp) {
			$eiu = new Eiu($this, $eiProp);
			if (null !== ($scalarEiProperty = $eiProp->getNature()->buildScalarEiProperty($eiu))) {
				$scalarEiProperties->offsetSet(EiPropPath::from($eiProp), $scalarEiProperty);
			}
		}
		return $scalarEiDefinition;
	}

	/**
	 * @throws \InvalidArgumentException
	 * @return IdNameDefinition
	 */
	function createIdNameDefinition(): IdNameDefinition {
		$idNameDefinition = new IdNameDefinition($this->eiMask, $this->eiMask->getLabelLstr());
		$idNameDefinition->setIdentityStringPattern($this->eiMask->getIdentityStringPattern());

		foreach ($this as $eiProp) {
			$eiPropPath = $eiProp->getEiPropPath();
			$eiPropNature = $eiProp->getNature();

			if (null !== ($idNameProp = $eiPropNature->buildIdNameProp(new Eiu($this->eiMask, $eiPropPath)))) {
				$idNameDefinition->putIdNameProp($eiPropPath, $idNameProp, EiPropPath::from($eiProp));
			}

			if (null !== ($idNamePropFork = $eiPropNature->buildIdNamePropFork(new Eiu($this->eiMask, $eiPropPath)))) {
				$idNameDefinition->putIdNamePropFork($eiPropPath, $idNamePropFork);
			}
		}

		return $idNameDefinition;
	}

	public function createFramedQuickSearchDefinition(EiFrame $eiFrame): QuickSearchDefinition {
		$quickSearchDefinition = new QuickSearchDefinition($this->getEiMask());

		foreach ($this as $eiProp) {
			$eiu = new Eiu($eiFrame, $eiProp);
			if (null !== ($quickSearchField = $eiProp->getNature()->buildQuickSearchProp($eiu))) {
				$quickSearchDefinition->putQuickSearchProp(EiPropPath::from($eiProp), $quickSearchField);
			}
		}

		return $quickSearchDefinition;
	}

//	public function createFramedFilterDefinition(EiFrame $eiFrame): FilterDefinition {
//		$eiu = new Eiu($eiFrame);
//
//		$filterDefinition = new FilterDefinition();
//		foreach ($this as $id => $eiProp) {
//			$filterProp = $eiProp->buildFilterProp($eiu);
//			ArgUtils::valTypeReturn($filterProp, FilterProp::class, $eiProp, 'buildManagedFilterProp', true);
//
//			if ($filterProp !== null) {
//				$filterDefinition->putFilterProp($id, $filterProp);
//			}
//		}
//		return $filterDefinition;
//	}
//
//	public function createFilterDefinition(N2nContext $n2nContext): FilterDefinition {
//		$eiu = new Eiu($n2nContext);
//
//		$filterDefinition = new FilterDefinition();
//		foreach ($this as $id => $eiProp) {
//			$filterProp = $eiProp->getNature()->buildFilterProp($eiu);
//
//			if ($filterProp !== null) {
//				$filterDefinition->putFilterProp($id, $filterProp);
//			}
//		}
//		return $filterDefinition;
//	}
//
	public function createFramedSortDefinition(EiFrame $eiFrame): SortDefinition {
		$sortDefinition = new SortDefinition();

		foreach ($this as $eiPropPathStr => $eiProp) {
			$eiu = new Eiu($eiFrame, $eiProp);

			if (null !== ($sortProp = $eiProp->getNature()->buildSortProp($eiu))) {
				$sortDefinition->putSortProp(EiPropPath::create($eiPropPathStr), $sortProp);
			}

			if (null !== ($sortPropFork = $eiProp->getNature()->buildSortPropFork($eiu))) {
				$sortDefinition->putSortPropFork(EiPropPath::create($eiPropPathStr), $sortPropFork);
			}
		}

		return $sortDefinition;
	}
//
//	public function createSortDefinition(N2nContext $n2nContext): SortDefinition {
//		$eiu = new Eiu($n2nContext);
//		$sortDefinition = new SortDefinition();
//
//		foreach ($this as $eiPropPathStr => $eiProp) {
//			if (null !== ($sortProp = $eiProp->getNature()->buildSortProp($eiu))) {
//				$sortDefinition->putSortProp($eiProp->getEiPropPath(), $sortProp);
//			}
//
//			if (null !== ($sortPropFork = $eiProp->getNature()->buildSortPropFork($eiu))) {
//				$sortDefinition->putSortPropFork($eiProp->getEiPropPath(), $sortPropFork);
//			}
//		}
//
//		return $sortDefinition;
//	}

	function createGuiDefinition(): GuiDefinition {
		$guiDefinition = new GuiDefinition($this->eiMask);

		foreach ($this as $eiProp) {
			$eiPropPath = $eiProp->getEiPropPath();

			if (null !== ($guiProp = $eiProp->getNature()->buildGuiProp(new Eiu($this->eiMask, $eiPropPath)))) {
				$guiDefinition->putGuiProp($eiPropPath, $guiProp, EiPropPath::from($eiProp));
			}
		}

		return $guiDefinition;
	}

	function createForkedEiFrame(EiPropPath $eiPropPath, EiForkLink $eiForkLink): EiFrame {
		$eiProp = $this->getByPath($eiPropPath);

		$parentEiFrame = $eiForkLink->getParent();
		$eiu = new Eiu($parentEiFrame, $eiForkLink->getParentEiObject(), $eiPropPath);
		$forkedEiFrame = $eiProp->getNature()->createForkedEiFrame($eiu, $eiForkLink);

		if ($forkedEiFrame->hasEiExecution()) {
			throw new EiException(TypeUtils::prettyMethName(get_class($eiProp), 'createForkedEiFrame')
					. ' must return an EiFrame which is not yet executed.');
		}

		if ($forkedEiFrame->getEiForkLink() !== $eiForkLink) {
			throw new EiException(TypeUtils::prettyMethName(get_class($eiProp), 'createForkedEiFrame')
					. ' must return an EiFrame with passed EiForkLink.');
		}

		$forkedEiFrame->setBaseUrl($parentEiFrame->getForkUrl(null, $eiPropPath,
				$eiForkLink->getMode(), $eiForkLink->getParentEiObject()));

		return $forkedEiFrame;
	}
}
