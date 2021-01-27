<?php
namespace rocket\impl\ei\component\command\iframe\controller;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\ui\Raw;
use rocket\ei\util\EiuCtrl;
use rocket\impl\ei\component\command\iframe\config\IframeConfig;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\util\magic\MagicObjectUnavailableException;
use n2n\util\type\CastUtils;
use n2n\web\http\controller\Controller;

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
		} /*else if (null !== $this->iframeConfig->getControllerLookupId()) {
			$eiuCtrl->forwardUrlIframeZone($this->getUrlToController(['src']));
		} */else {
			$eiuCtrl->forwardIframeZone(new Raw($this->iframeConfig->getSrcDoc()), $this->iframeConfig->isUseTemplate());
		}
	}
	
// 	function doSrc(array $params = []) {
// 		$eiuCtrl = EiuCtrl::from($this->cu());
// 		$controller = null;
// 		try {
// 			$controller = $eiuCtrl->eiu()->lookup($controllerLookupId);
// 		} catch (MagicObjectUnavailableException $e) {
// 			throw new InvalidEiComponentConfigurationException($this->eiCommand . ' invalid configured.', 0, $e);
// 		}
		
// 		if (!($controller instanceof Controller)) {
// 			throw new InvalidEiComponentConfigurationException($this->eiCommand . ' invalid configured. ' 
// 					. get_class($controller) . ' does not implement ' + Controller::class, 0, $e);
// 		}
		
// 		$this->delegate($controller);
// 	}
	
	
}