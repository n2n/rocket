<?php

namespace rocket\op\ei\manage\gui;

use rocket\op\ei\EiPropPath;
use rocket\ui\gui\GuiProp;
use rocket\op\ei\manage\DefPropPath;
use rocket\ui\gui\field\GuiFieldMap;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\ui\gui\GuiEntry;
use rocket\ui\gui\field\GuiField;
use rocket\ui\gui\ViewMode;
use rocket\op\ei\util\Eiu;

class EiGuiPropMap {

	/**
	 * @var EiGuiPropWrapper[]
	 */
	private array $eiGuiPropWrappers = array();

	function __construct(private EiGuiDefinition $eiGuiDefinition) {

	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @param EiGuiProp $eiGuiProp
	 */
	function putEiGuiProp(EiPropPath $eiPropPath, EiGuiProp $eiGuiProp): void {
		$eiPropPathStr = (string) $eiPropPath;

		if (isset($this->eiGuiPropWrappers[$eiPropPathStr])) {
			throw new EiGuiException('GuiProp for EiPropPath \'' . $eiPropPathStr . '\' is already registered');
		}

		$this->eiGuiPropWrappers[$eiPropPathStr] = new EiGuiPropWrapper($this->eiGuiDefinition, $eiPropPath, $eiGuiProp);
	}

	/**
	 * @return EiPropPath[]
	 */
	function getEiPropPaths(): array {
		return array_map(fn (EiGuiPropWrapper $w) => $w->getEiPropPath(), $this->eiGuiPropWrappers);
	}

	/**
	 * @param EiPropPath $eiPropPath
	 */
	function removeGuiProp(EiPropPath $eiPropPath): void {
		$eiPropPathStr = (string) $eiPropPath;

		unset($this->eiGuiPropWrappers[$eiPropPathStr]);
	}

	function containsEiPropPath(EiPropPath $eiPropPath): bool {
		return isset($this->eiGuiPropWrappers[(string) $eiPropPath]);
	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @return EiGuiPropWrapper
	 * @throws EiGuiException
	 */
	function getGuiPropWrapper(EiPropPath $eiPropPath): EiGuiPropWrapper {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->eiGuiPropWrappers[$eiPropPathStr])) {
			throw new EiGuiException('No GuiProp with id \'' . $eiPropPathStr . '\' registered');
		}

		return $this->eiGuiPropWrappers[$eiPropPathStr];
	}

	/**
	 * @return DisplayDefinition[]
	 */
	function compileAllDefaultDisplayDefinitions(): array {
		return $this->rCompileDefaultDisplayDefinitions(new DefPropPath([]), $this);
	}

	private function rCompileDefaultDisplayDefinitions(DefPropPath $parentDefPropPath, EiGuiPropMap $eiGuiPropMap): array {
		$displayDefinitions = [];
		foreach ($eiGuiPropMap->eiGuiPropWrappers as $eiGuiPropWrapper) {
			$defPropPath = $parentDefPropPath->ext($eiGuiPropWrapper->getEiPropPath());
			$displayDefinition = $eiGuiPropWrapper->getDisplayDefinition();

			if (null !== $displayDefinition && $displayDefinition->isDefaultDisplayed()) {
				$displayDefinitions[(string) $defPropPath] = $displayDefinition;
				continue;
			}

			if (null !== ($childEiGuiPropMap = $eiGuiPropWrapper->getChildEiGuiPropMap())) {
				$displayDefinitions = array_merge($displayDefinitions, $this->rCompileDefaultDisplayDefinitions($defPropPath, $childEiGuiPropMap));
			}
		}

		return $displayDefinitions;
	}

	/**
	 * @return GuiProp[]
	 */
	function compileAllGuiProps(): array {
		$deter = new ContextGuiPropDeterminer();
		$guiProps = $this->rCompileGuiProps(new DefPropPath([]), $this, $deter);
		return $deter->applyContextGuiProps($guiProps);
	}

	/**
	 * @return GuiProp[]
	 */
	private function rCompileGuiProps(DefPropPath $parentDefPropPath, EiGuiPropMap $eiGuiPropMap, ContextGuiPropDeterminer $deter): array {
		$guiProps = [];
		foreach ($eiGuiPropMap->eiGuiPropWrappers as $eiGuiPropWrapper) {
			$defPropPath = $parentDefPropPath->ext($eiGuiPropWrapper->getEiPropPath());
			$guiProps[(string) $defPropPath] = $this->createGuiProp($defPropPath, $eiGuiPropWrapper->getEiGuiProp());

			$deter->reportDefPropPath($defPropPath);

			if (null !== ($childEiGuiPropMap = $eiGuiPropWrapper->getChildEiGuiPropMap())) {
				$guiProps = array_merge($guiProps, $this->rCompileGuiProps($defPropPath, $childEiGuiPropMap, $deter));
			}
		}
		return $guiProps;
	}

	private function createGuiProp(DefPropPath $defPropPath, EiGuiProp $eiGuiProp): GuiProp {
		$displayDefinition = $eiGuiProp->getDisplayDefinition();
		$guiProp = new GuiProp($displayDefinition?->getLabel() ?? 'unused label TODO', $displayDefinition?->getHelpText());

		$forkEiGuiPropMap = $eiGuiProp->getForkEiGuiPropMap();
		if ($forkEiGuiPropMap === null) {
			return $guiProp;
		}

		$descendantGuiPropNames = [];
		foreach ($forkEiGuiPropMap->getEiPropPaths() ?? [] as $eiPropPath) {
			$descendantGuiPropNames[] = (string) $defPropPath->ext($eiPropPath)->toGuiFieldPath();
		}

		$guiProp->setDescendantGuiPropNames($descendantGuiPropNames);
		return $guiProp;
	}


	function createGuiFieldMap(EiFrame $eiFrame, EiEntry $eiEntry): GuiFieldMap {
		$guiFieldMap = new GuiFieldMap();
		foreach ($this->eiGuiPropWrappers as $eiGuiPropWrapper) {
			$guiField = $eiGuiPropWrapper->buildGuiField($eiFrame, $eiEntry, null);

			if ($guiField !== null) {
				$guiFieldMap->putGuiField($eiGuiPropWrapper->getEiPropPath()->toGuiFieldKey(), $guiField);
			}
		}
		return $guiFieldMap;
	}

	/**
	 * @param DefPropPath $defPropPath
	 * @return boolean
	 */
	function containsDefPropPath(DefPropPath $defPropPath): bool {
		$eiPropPaths = $defPropPath->toArray();
		$guiDefinition = $this;
		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
			if (empty($eiPropPaths)) {
				return $guiDefinition->containsEiPropPath($eiPropPath);
			}

			$guiDefinition = $guiDefinition->getGuiPropFork($eiPropPath)->getForkedEiGuiDefinition();
		}

		return true;
	}

	function getEiGuiPropByDefPropPath(DefPropPath $defPropPath): EiGuiProp {
		$eiPropPaths = $defPropPath->toArray();

		$eiGuiPropMap = $this;
		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
			$guiEiProp = $eiGuiPropMap->getGuiPropWrapper($eiPropPath)->getEiGuiProp();
			if (empty($eiPropPaths)) {
				return $guiEiProp;
			}

			$eiGuiPropMap = $guiEiProp->getForkEiGuiPropMap();
		}

		throw new EiGuiException('No GuiProp with for path \'' . $defPropPath . '\' registered');
	}

	/**
	 * @return DefPropPath[]
	 */
	function compileAllDefPropPaths(): array {
		$defPropPaths = [];
		foreach ($this->eiGuiPropWrappers as $eiGuiPropWrapper) {
			$defPropPath = new DefPropPath([$eiGuiPropWrapper->getEiPropPath()]);
			$defPropPaths[] = $defPropPath;

			foreach ($eiGuiPropWrapper->getEiGuiProp()->getForkEiGuiPropMap()?->compileAllDefPropPaths() as $forkDefPropPath) {
				$defPropPaths[] = $defPropPath->ext($forkDefPropPath);
			}
		}
		return $defPropPaths;
	}

}


class ContextGuiPropDeterminer {
	/**
	 * @var DefPropPath[]
	 */
	private $defPropPaths = [];
	/**
	 * @var DefPropPath[]
	 */
	private $forkDefPropPaths = [];
	private $forkedDefPropPaths = [];

	function __construct(/*private readonly EiGuiDefinition $eiGuiDefinition*/) {

	}

	/**
	 * @param DefPropPath $defPropPath
	 */
	function reportDefPropPath(DefPropPath $defPropPath) {
		$defPropPathStr = (string) $defPropPath;

		$this->defPropPaths[$defPropPathStr] = $defPropPath;
		unset($this->forkDefPropPaths[$defPropPathStr]);
		unset($this->forkedDefPropPaths[$defPropPathStr]);

		$forkDefPropPath = $defPropPath;
		while ($forkDefPropPath->hasMultipleEiPropPaths()) {
			$forkDefPropPath = $forkDefPropPath->getPoped();
			$this->reportFork($forkDefPropPath, $defPropPath);
		}
	}

	/**
	 * @param DefPropPath $forkDefPropPath
	 * @param DefPropPath $defPropPath
	 */
	private function reportFork(DefPropPath $forkDefPropPath, DefPropPath $defPropPath) {
		$forkDefPropPathStr = (string) $forkDefPropPath;

		if (isset($this->defPropPaths[$forkDefPropPathStr])) {
			return;
		}

		if (!isset($this->forkDefPropPaths[$forkDefPropPathStr])) {
			$this->forkDefPropPaths[$forkDefPropPathStr] = [];
		}
		$this->forkedDefPropPaths[$forkDefPropPathStr][] = $defPropPath;
		$this->forkDefPropPaths[$forkDefPropPathStr] = $forkDefPropPath;

		if ($forkDefPropPath->hasMultipleEiPropPaths()) {
			$this->reportFork($forkDefPropPath->getPoped(), $forkDefPropPath);
		}
	}

	function applyContextGuiProps(array $guiProps/*, N2nLocale $n2nLocale*/): array {

		foreach ($this->forkDefPropPaths as $defPropPathStr => $forkDefPropPath) {
			$guiProp = $guiProps[$defPropPathStr];

//			$eiProp = $this->eiGuiDefinition->getEiGuiPropWrapperByDefPropPath($forkDefPropPath)->getEiProp();


//			$guiProp = (new GuiProp($eiProp->getNature()->getLabelLstr()->t($n2nLocale)))
//					->setDescendantGuiPropNames(array_map(
//							function ($defPropPath) { return (string) $defPropPath; },
//							$this->forkedDefPropPaths[(string) $forkDefPropPath]));
//
//			if (null !== ($helpTextLstr = $eiProp->getNature()->getHelpTextLstr())) {
//				$guiProp->setHelpText($helpTextLstr->t($n2nLocale));
//			}

			$guiProps[$defPropPathStr] = $guiProp;
		}

		return $guiProps;
	}
}