<?php
namespace rocket\ui\gui;

use rocket\ui\si\content\SiGui;
use rocket\ui\si\api\request\SiInput;
use rocket\si\input\SiInputError;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\content\SiValueBoundary;

interface Gui {

	function getSiGui(): SiGui;

	/**
	 * @param SiInput $siInput
	 * @return SiInputError|null
	 * @throws CorruptedSiDataException
	 */
//	function handleSiInput(SiInput $siInput): ?SiInputError;

	/**
	 * @return SiValueBoundary[]
	 */
//	function getInputSiValueBoundaries(): array;
//
//	/**
//	 * @throws CorruptedSiInputDataException
//	 */
//	function handleSiCall(ZoneApiControlCallId $zoneControlCallId): SiCallResponse;
}

//class EiGui {
//	/**
//	 * @var EiGuiDeclaration
//	 */
//	private $eiGuiDeclaration;
//	/**
//	 * @var EiGuiValueBoundary[]
//	 */
//	private $eiGuiValueBoundaries = [];
//
//	/**
//	 * @param EiMask $eiMask
//	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
//	 */
//	function __construct(EiGuiDeclaration $eiGuiDeclaration) {
//		$this->eiGuiDeclaration = $eiGuiDeclaration;
//	}
//
//	/**
//	 * @return EiGuiDeclaration
//	 */
//	function getEiGuiDeclaration() {
//		return $this->eiGuiDeclaration;
//	}
//
//	/**
//	 * @return boolean
//	 */
//	function hasMultipleEiGuiValueBoundaries() {
//		return count($this->eiGuiValueBoundaries) > 1;
//	}
//
//	/**
//	 * @return boolean
//	 */
//	function hasSingleEiGuiValueBoundary() {
//		return count($this->eiGuiValueBoundaries) === 1;
//	}
//
//	function isEmpty() {
//		return empty($this->eiGuiValueBoundaries);
//	}
//
//	/**
//	 * @return null|EiGuiValueBoundary
//	 */
//	function getEiGuiValueBoundary() {
//		if ($this->isEmpty()) {
//			return null;
//		}
//
//		if ($this->hasSingleEiGuiValueBoundary()) {
//			return current($this->eiGuiValueBoundaries);
//		}
//
//		throw new IllegalStateException('EiGui contains multiple EiGuiValueBoundaries.');
//	}
//
//	function getEiGuiValueBoundaries() {
//		return $this->eiGuiValueBoundaries;
//	}
//
//	/**
//	 * @param EiFrame $eiFrame
//	 * @param EiEntry[] $eiEntries
//	 * @param int $treeLevel
//	 * @return \rocket\op\ei\manage\gui\EiGuiValueBoundary
//	 *@throws \InvalidArgumentException
//	 */
//	function appendEiGuiValueBoundary(EiFrame $eiFrame, array $eiEntries, ?int $treeLevel = null) {
//		return $this->eiGuiValueBoundaries[] = $this->eiGuiDeclaration->createEiGuiValueBoundary($eiFrame, $eiEntries, $this, $treeLevel);
//	}
//
//	/**
//	 * @param EiFrame $eiFrame
//	 * @param int $treeLevel
//	 * @throws InaccessibleEiEntryException
//	 */
//	function appendNewEiGuiValueBoundary(EiFrame $eiFrame, ?int $treeLevel = null) {
//		return $this->eiGuiValueBoundaries[] = $this->eiGuiDeclaration->createNewEiGuiValueBoundary($eiFrame, $this, $treeLevel);
//	}
//
//	/**
//	 * @param bool $siControlsIncluded
//	 * @return \rocket\si\content\SiValueBoundary
//	 *@throws IllegalStateException
//	 */
//	function createSiEntry(EiFrame $eiFrame, bool $siControlsIncluded = true) {
//		if ($this->hasSingleEiGuiValueBoundary()) {
//			return $this->eiGuiDeclaration->createSiEntry($eiFrame, current($this->eiGuiValueBoundaries), $siControlsIncluded);
//		}
//
//		throw new IllegalStateException('EiGuiDeclaration has none or multiple EiGuiValueBoundaries');
//	}
//
//	/**
//	 * @return \rocket\si\content\SiValueBoundary[]
//	 */
//	function createSiEntries(EiFrame $eiFrame, bool $siControlsIncluded = true) {
//		$siEntries = [];
//		foreach ($this->eiGuiValueBoundaries as $eiGuiValueBoundary) {
//			$siValueBoundary = $siEntries[] = $this->eiGuiDeclaration->createSiEntry($eiFrame, $eiGuiValueBoundary, $siControlsIncluded);
//		}
//		return $siEntries;
//	}
//
//
//}
