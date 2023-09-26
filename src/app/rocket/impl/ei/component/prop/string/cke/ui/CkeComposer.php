<?php
namespace rocket\impl\ei\component\prop\string\cke\ui;

use n2n\util\type\ArgUtils;
use n2n\util\EnumUtils;

class CkeComposer {
	private CkeMode $mode = CkeMode::NORMAL;
	private bool $bbcodeEnabled = false;
	private bool $tableEnabled = false;

	public function mode(CkeMode|string $mode): static {
		if (is_string($mode)) {
			$mode = EnumUtils::backedToUnit($mode, CkeMode::class);
		}
		$this->mode = $mode;
		return $this;
	}
	/**
	 * @param bool $table
	 * @return CkeComposer
	 */
	public function table(bool $table): static {
		$this->tableEnabled = $table;
		return $this;
	}

	public function bbcode(bool $bbcode): static {
		$this->bbcodeEnabled = $bbcode;
		return $this;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\string\cke\ui\CkeConfig
	 */
	public function toCkeConfig(): CkeConfig {
		return new CkeConfig($this->mode, $this->tableEnabled, $this->bbcodeEnabled);
	}
}
