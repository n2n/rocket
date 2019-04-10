<?php
namespace rocket\ei\util\frame;

use rocket\ei\component\command\EiCommand;
use rocket\ei\manage\control\ControlButton;
use n2n\util\uri\Url;
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
	 * @param mixed $urlExt
	 * @return \n2n\util\uri\Url
	 */
	private function createUrl($urlExt) {
		return $this->eiuFrame->getHttpContext()
				->getControllerContextPath($this->eiuFrame->getEiFrame()->getControllerContext())
				->ext((string) EiCommandPath::from($this->eiCommand))->toUrl()->ext($urlExt);
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @param ControlButton $controlButton
	 * @param Url $urlExt
	 * @return \rocket\ei\manage\control\JhtmlControl
	 */
	public function createZone(ControlButton $controlButton, $urlExt = null) {
		return new SiControl($this->createUrl($urlExt), $controlButton);
	}
	
	public function createCallback(ControlButton $controlButton, \Closure $callback) {
		return new SiCallbackControl($callback, $controlButton);
	}
	
	public function createHref(ControlButton $controlButton, $urlExt = null) {
		return new HrefControl($this->createUrl($urlExt), $controlButton);
	}
	
	public function createGroup(ControlButton $controlButton): GroupControl {
		return new GroupControl($controlButton);
	}
	
	public function createDeactivated(ControlButton $controlButton) {
		return new DeactivatedControl($controlButton);
	}
}