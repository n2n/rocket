<?php
namespace rocket\impl\ei\component\command\iframe;

use n2n\web\http\controller\Controller;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\command\adapter\IndependentEiCommandAdapter;
use rocket\impl\ei\component\command\iframe\config\IframeConfig;
use rocket\impl\ei\component\command\iframe\controller\IframeController;
use rocket\si\control\SiButton;
use rocket\si\control\SiIconType;

class IframeEiCommand extends IndependentEiCommandAdapter {
	const CONTROL_RUN_KEY = 'run';

	private IframeConfig $iframeConfig;

	/**
	 * IframeEiCommand constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->iframeConfig = new IframeConfig();
	}

	protected function prepare() {
		$this->getConfigurator()
				->addAdaption($this->iframeConfig);
	}

	function createGeneralGuiControls(Eiu $eiu): array {
		$buttonLabel = $this->iframeConfig->getButtonLabel() ?? $eiu->dtc('rocket')->t('run_txt');
		$siButton = SiButton::success($buttonLabel, $this->iframeConfig->getButtonIcon() ?? SiIconType::ICON_PLAY)
				->setTooltip($this->iframeConfig->getButtonTooltip());

		$siControl = $eiu->factory()->controls()->newCmdRef(self::CONTROL_RUN_KEY, $siButton);

		return [$siControl];
	}
	
	function createEntryGuiControls(Eiu $eiu): array {
		return [];
	}

	function lookupController(Eiu $eiu): ?Controller {
		return new IframeController($this->iframeConfig);
	}
}