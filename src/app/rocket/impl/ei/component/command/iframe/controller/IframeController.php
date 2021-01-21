<?php
namespace rocket\impl\ei\component\command\iframe\controller;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\ui\Raw;
use rocket\ei\util\EiuCtrl;
use rocket\impl\ei\component\command\iframe\config\IframeConfig;

class IframeController extends ControllerAdapter {
	/**
	 * @var IframeConfig
	 */
	private $iframeConfig;

	/**
	 * IframeController constructor.
	 * @param IframeConfig $iframeConfig
	 */
	public function __construct(IframeConfig $iframeConfig) {
		$this->iframeConfig = $iframeConfig;
	}

	function index() {
		$eiuCtrl = EiuCtrl::from($this->cu());
		
		if (null !== ($url = $this->iframeConfig->getUrl())) {
			$eiuCtrl->forwardUrlIframeZone($url);
		} else {
			$eiuCtrl->forwardIframeZone(new Raw($this->iframeConfig->getSrcDoc()), $this->iframeConfig->isUseTemplate());
		}
	}
}