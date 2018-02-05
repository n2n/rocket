<?php
namespace rocket\impl\ei\component\prop\string\cke\ui;

use rocket\impl\ei\component\prop\string\cke\CkeEiProp;

class CkeConfig {
	private $mode;
	private $tableEnabled;
	private $bbcodeEnabled;
	
	public function __construct(string $mode, bool $tablesEnabled) {
		$this->mode = $mode;
		$this->tableEnabled = $tablesEnabled;
	}
	
	public function getMode() {
		return $this->mode;
	}
	
	public function isTablesEnabled() {
		return $this->tableEnabled;
	}
	
	public function isBbcodeEnabled() {
		return $this->bbcodeEnabled;
	}
	
	public static function createDefault() {
		return new CkeConfig(CkeEiProp::MODE_NORMAL, false, false);
	}
}