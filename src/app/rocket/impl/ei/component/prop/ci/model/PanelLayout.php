<?php
namespace rocket\impl\ei\component\prop\ci\model;

use rocket\si\content\impl\relation\SiEmbeddedEntry;
use rocket\si\content\impl\relation\SiPanel;
use rocket\si\content\impl\relation\SiGridPos;
use rocket\ei\util\frame\EiuFrame;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;

class PanelLayout {
	/**
	 * @var SiPanel[]
	 */
	private $siPanels = [];
	
	public function __construct() {	
	}
	
	/**
	 * @param PanelDeclaration[] $panelDeclarations
	 */
	function assignConfigs(array $panelDeclarations, EiuFrame $targetEiuFrame, RelationModel $relationModel) {
		$numGridCols = 0;
		$numGridRows = 0;
		
		foreach ($panelDeclarations as $panelDeclaration) {
			$gridPos = $panelDeclaration->getGridPos();
			
			if ($gridPos === null) continue;
			
			$colEnd = $gridPos->getColEnd();
			if ($numGridCols < $colEnd) {
				$numGridCols = $colEnd;
			}
			
			$rowEnd = $gridPos->getRowEnd();
			if ($numGridRows < $rowEnd) {
				$numGridRows = $rowEnd;
			}
		}
		
		$this->siPanels = [];
		foreach ($panelDeclarations as $panelDeclaration) {
			$this->siPanels[$panelDeclaration->getName()] = $siPanel = new SiPanel($panelDeclaration->getName(), 
					$panelDeclaration->getLabel());
			
			$this->configSiPanel($siPanel, $panelDeclaration, $targetEiuFrame, $relationModel);
			
			if (($gridPos = $panelDeclaration->getGridPos()) !== null) {
				$siPanel->setGridPos($gridPos->toSiGridPos());
				continue;
			}
			
			$siPanel->setGridPos(new SiGridPos(1, $numGridCols,
					++$numGridRows, $numGridRows));
		}
	}
	
	/**
	 * @param SiPanel $siPanel
	 * @param PanelDeclaration $panelDeclaration
	 * @param EiuFrame $targetEiuFrame
	 * @param RelationModel $relationModel
	 */
	private function configSiPanel($siPanel, $panelDeclaration, $targetEiuFrame, $relationModel) {
		$allowedSiTypeQualifiers = [];
		foreach ($targetEiuFrame->engine()->mask()->possibleMasks() as $eiuMask) {
			if ($panelDeclaration->isEiuMaskAllowed($eiuMask)) {
				$allowedSiTypeQualifiers[] = $eiuMask->createSiTypeQualifier();
			} 
		}
		
		$siPanel->setSortable(true)
// 				->setReduced($relationModel->isReduced())
				->setAllowedTypeQualifiers($allowedSiTypeQualifiers);
	}
	
	/**
	 * @param string $panelName
	 * @return boolean
	 */
	function containsPanelName(string $panelName) {
		return isset($this->siPanels[$panelName]);
	}
	
	function clearSiEmbeddedEntries() {
		foreach ($this->siPanels as $siPanel) {
			$siPanel->setEmbeddedEntries([]);
		}
	}
	
	/**
	 * @param string $panelName
	 * @param SiEmbeddedEntry $siEmbeddedEntry
	 * @return boolean
	 */
	function addSiEmbeddedEntry(string $panelName, SiEmbeddedEntry $siEmbeddedEntry) {
		if (!isset($this->siPanels[$panelName])) {
			return false;
		}
		
		$this->siPanels[$panelName]->addEmbeddedEntry($siEmbeddedEntry);
		return true;
	}
	
	/**
	 * @return bool
	 */
	public function hasGrid() {
		return $this->numGridCols > 0 || $this->numGridRows > 0;
	}
	
	public function getNumGridCols() {
		return $this->numGridCols;
	}
	
	public function getNumGridRows() {
		return $this->numGridCols;
	}
	
	/**
	 * @return \rocket\si\content\impl\relation\SiPanel[]
	 */
	public function toSiPanels() {
		return array_values($this->siPanels);
	}
}

