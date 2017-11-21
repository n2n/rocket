<?php
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\component\command\EiCommand;
use rocket\spec\ei\manage\control\ControlButton;
use n2n\util\uri\Url;
use rocket\spec\ei\manage\control\JhtmlControl;

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
	 * @return \rocket\spec\ei\manage\control\JhtmlControl
	 */
	public function createJhtml(ControlButton $controlButton, $urlExt = null) {
		$url = $this->eiuFrame->getHttpContext()
				->getControllerContextPath($this->eiuFrame->getEiFrame()->getControllerContext())
				->ext($this->eiCommand->getId())->toUrl()->ext($urlExt);
		return new JhtmlControl($url, $controlButton);
	}
}