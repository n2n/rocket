<?php
namespace rocket\impl\ei\component\prop\ci\model;

use rocket\si\content\SiEmbeddedEntry;
use rocket\si\content\impl\SiPanel;
use rocket\si\content\impl\SiGridPos;

class PanelLayout {
	/**
	 * @var SiPanel[]
	 */
	private $siPanels = [];
	
	public function __construct() {	
	}
	
	/**
	 * @param PanelConfig[] $panelConfigs
	 */
	function assignConfigs(array $panelConfigs) {
		$numGridCols = 0;
		$numGridRows = 0;
		
		foreach ($panelConfigs as $panelConfig) {
			$gridPos = $panelConfig->getGridPos();
			
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
		foreach ($panelConfigs as $panelConfig) {
			$this->siPanels[$panelConfig->getName()] = $siPanel = new SiPanel($panelConfig->getName(), 
					$panelConfig->getLabel());
			
			if (($gridPos = $panelConfig->getGridPos()) !== null) {
				$siPanel->setGridPos($gridPos->toSiGridPos());
				continue;
			}
			
			$siPanel->setGridPos(new SiGridPos(1, $numGridCols,
					++$numGridRows, $numGridRows));
		}
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
	 * @return SiPanel[]
	 */
	public function getSiPanels() {
		return $this->panelConfigs;
	}
}

