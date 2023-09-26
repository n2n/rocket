<?php
namespace rocket\impl\ei\component\prop\string\cke\ui;

use rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig;
use n2n\util\type\ArgUtils;
use rocket\impl\ei\component\prop\string\cke\model\CkeLinkProvider;

class CkeConfig {
	/**
	 * @var array<CkeLinkProvider>
	 */
	private array $linkProviders;

	function __construct(private CkeMode $mode = CkeMode::SIMPLE, private bool $tableEnabled = false,
			private bool $bbcodeEnabled = false, private ?CkeCssConfig $cssConfig = null,
			array $linkProviders = []) {
		$this->setLinkProviders($linkProviders);
	}

	public function getMode(): CkeMode {
		return $this->mode;
	}

	public function isTablesEnabled(): bool {
		return $this->tableEnabled;
	}

	public function isBbcodeEnabled(): bool {
		return $this->bbcodeEnabled;
	}

	function setCssConfig(?CkeCssConfig $ckeCssConfig): static {
		$this->cssConfig = $ckeCssConfig;
		return $this;
	}

	function getCssConfig(): ?CkeCssConfig {
		return $this->cssConfig;
	}

	/**
	 * @param array<CkeLinkProvider> $ckeLinkProviders
	 * @return void
	 */
	function setLinkProviders(array $ckeLinkProviders): void {
		ArgUtils::valArray($ckeLinkProviders, CkeLinkProvider::class);
		$this->linkProviders = $ckeLinkProviders;
	}

	/**
	 * @return CkeLinkProvider[]
	 */
	function getLinkProviders(): array {
		return $this->linkProviders;
	}

	/**
	 * @return array<CkeMode>
	 * @deprecated use {@link CkeMode::cases()}.
	 */
	static function getModes(): array {
		return CkeMode::cases();
	}

	public static function createDefault(): CkeConfig {
		return new CkeConfig(CkeMode::NORMAL, false, false);
	}

}
