<?php
namespace rocket\spec\ei\manage\util\model;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\component\command\EiCommand;
use rocket\spec\ei\manage\control\ControlButton;
use n2n\util\uri\Url;
use rocket\spec\ei\manage\control\JhtmlControl;

class EiuControlFactory {
	private $eiuFrame;
	private $view;
	
	public function __construct(EiuFrame $eiuFrame, HtmlView $view) {
		$this->eiuFrame = $eiuFrame;
		$this->view = $view;
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @param ControlButton $controlButton
	 * @param Url $urlExt
	 * @return \rocket\spec\ei\manage\control\JhtmlControl
	 */
	public function createAjah(EiCommand $eiCommand, ControlButton $controlButton, Url $urlExt = null) {
		$url = $this->view->getHttpContext()
				->getControllerContextPath($this->eiuFrame->getEiFrame()->getControllerContext())
				->ext($eiCommand->getId())->toUrl()->ext($urlExt);
		return new JhtmlControl($url, $controlButton);
	}
}