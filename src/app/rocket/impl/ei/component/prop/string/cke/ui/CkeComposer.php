<?php
namespace rocket\impl\ei\component\prop\string\cke\ui;

use rocket\impl\ei\component\prop\string\cke\CkeEiProp;
use n2n\util\type\ArgUtils;

class CkeComposer {
	private $mode = CkeEiProp::MODE_NORMAL;
	private $bbcodeEnabled = false;
	private $tableEnabled = false;
	
	/**
	 * @param string $mode
	 * @return \rocket\impl\ei\component\prop\string\cke\ui\CkeComposer
	 */
	public function mode(string $mode) {
		ArgUtils::valEnum($mode, CkeEiProp::getModes());
		$this->mode = $mode;
		return $this;
	}
	/**
	 * @param bool $table
	 * @return \rocket\impl\ei\component\prop\string\cke\ui\CkeComposer
	 */
	public function table(bool $table) {
		$this->tableEnabled = $table;
		return $this;
	}

	public function bbcode(bool $bbcode) {
		$this->bbcodeEnabled = $bbcode;
		return $this;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\string\cke\ui\CkeConfig
	 */
	public function toCkeConfig() {
		return new CkeConfig($this->mode, $this->tableEnabled, $this->bbcodeEnabled);
	}
}