<?php
namespace rocket\impl\ei\component\prop\ci\model;

class PanelLayout {
	private $numGridCols = 0;
	private $numGridRows = 0;
	/**
	 * @var PanelConfig[]
	 */
	private $panelConfigs;
	
	/**
	 * @param PanelConfig[] $panelConfigs
	 */
	public function __construct(array $panelConfigs) {
		$this->panelConfigs = $panelConfigs;
		
		$this->dingselGrid();
	}
	
	private function dingselGrid() {
		foreach ($this->panelConfigs as $panelConfig) {
			$gridPos = $panelConfig->getGridPos();
			
			if ($gridPos === null) continue;
			
			$colEnd = $gridPos->getColEnd();
			if ($this->numGridCols < $colEnd) {
				$this->numGridCols = $colEnd;
			}
			
			$rowEnd = $gridPos->getRowEnd();
			if ($this->numGridRows < $rowEnd) {
				$this->numGridRows = $rowEnd;
			}
		}
		
		if (!$this->hasGrid()) return;
		
		foreach ($this->panelConfigs as $panelConfig) {
			if ($panelConfig->getGridPos() !== null) continue;
			
			$panelConfig->setGridPos(new GridPos(1, $this->numGridCols, 
					++$this->numGridRows, $this->numGridRows));
		}
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
	 * @return PanelConfig[]
	 */
	public function getPanelConfigs() {
		return $this->panelConfigs;
	}
}

