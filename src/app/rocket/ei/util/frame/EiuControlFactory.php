<?php
namespace rocket\ei\util\frame;

use rocket\ei\component\command\EiCommand;
use rocket\ei\manage\control\ControlButton;
use n2n\util\uri\Url;
use rocket\ei\manage\control\JhtmlControl;
use rocket\ei\manage\control\GroupControl;
use rocket\ei\manage\control\DeactivatedControl;
use rocket\ei\EiCommandPath;

class EiuControlFactory {
	private $eiuFrame;
	private $eiCommand;
	
	public function __construct(EiuFrame $eiuFrame, EiCommand $eiCommand) {
		$this->eiuFrame = $eiuFrame;
		$this->eiCommand = $eiCommand;
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @param ControlButton $controlButton
	 * @param Url $urlExt
	 * @return \rocket\ei\manage\control\JhtmlControl
	 */
	public function createJhtml(ControlButton $controlButton, $urlExt = null) {
		$url = $this->eiuFrame->getHttpContext()
				->getControllerContextPath($this->eiuFrame->getEiFrame()->getControllerContext())
				->ext((string) EiCommandPath::from($this->eiCommand))->toUrl()->ext($urlExt);
		return new JhtmlControl($url, $controlButton);
	}
	
	public function createGroup(ControlButton $controlButton): GroupControl {
		return new GroupControl($controlButton);
	}
	
	public function createDeactivated(ControlButton $controlButton) {
		return new DeactivatedControl($controlButton);
	}
}