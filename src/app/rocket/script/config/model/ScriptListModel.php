<?php
namespace rocket\script\config\model;

use rocket\script\core\ScriptManager;
use n2n\dispatch\Dispatchable;
use rocket\script\core\extr\ScriptExtraction;

class ScriptListModel implements Dispatchable {
	private $scriptManager;
	private $scriptExtractionGroups = array();
	
	public function __construct(ScriptManager $scriptManager) {
		$this->scriptManager = $scriptManager;
		foreach ($scriptManager->extractScripts() as $scriptExtraction) {
			$moduleNamespace = (string) $scriptExtraction->getModule();
			if (!isset($this->scriptExtractionGroups[$moduleNamespace])) {
				$this->scriptExtractionGroups[$moduleNamespace] = array();
			}
			
			$this->scriptExtractionGroups[$moduleNamespace][$scriptExtraction->getId()] = $scriptExtraction;
		}
	}
	
	public function getScriptExtractionGroups() {
		return $this->scriptExtractionGroups;
	}
	
	public function isSealed(ScriptExtraction $scriptExtraction) {
		return $this->scriptManager->isScriptOfIdSealed($scriptExtraction->getId());
	}
}