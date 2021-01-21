<?php
namespace rocket\impl\ei\component\command\iframe;

use rocket\impl\ei\component\command\adapter\IndependentEiCommandAdapter;
use rocket\impl\ei\component\command\iframe\config\IframeConfig;

class IframeEiCommand extends IndependentEiCommandAdapter {
	private IframeConfig $iframeConfig;

	/**
	 * IframeEiCommand constructor.
	 * @param IframeConfig $iframeConfig
	 */
	public function __construct() {
		parent::__construct();

		$this->iframeConfig = new IframeConfig();
	}

	protected function prepare() {
		$this->getConfigurator()
			->addAdaption($this->iframeConfig);
	}
}