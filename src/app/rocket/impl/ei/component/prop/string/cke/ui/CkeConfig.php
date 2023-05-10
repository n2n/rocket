<?php
namespace rocket\impl\ei\component\prop\string\cke\ui;

class CkeConfig {

	const MODE_SIMPLE = 'simple';
	const MODE_NORMAL = 'normal';
	const MODE_ADVANCED = 'advanced';

	function __construct(private string $mode = self::MODE_SIMPLE, private bool $tableEnabled = false,
			private bool $bbcodeEnabled = false) {
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

	static function getModes(): array {
		return [self::MODE_SIMPLE, self::MODE_NORMAL, self::MODE_ADVANCED];
	}

	public static function createDefault() {
		return new CkeConfig(self::MODE_NORMAL, false, false);
	}

}
