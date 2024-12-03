<?php
namespace rocket\impl\ei\component\cmd\iframe\controller;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\PageNotFoundException;
use n2n\web\ui\Raw;
use rocket\op\util\OpuCtrl;
use rocket\impl\ei\component\cmd\iframe\config\IframeConfig;
use rocket\op\ei\component\InvalidEiConfigurationException;
use n2n\util\magic\MagicObjectUnavailableException;
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

	function index(?int $pid = null) {
		$opuCtrl = OpuCtrl::from($this->cu());

		$this->verifyPid($pid);

		if (null !== ($url = $this->iframeConfig->getUrl())) {
			$url = $url->ext($pid);
			$opuCtrl->forwardUrlIframeZone($url, $this->iframeConfig->getWindowTitle());
		} else if (null !== ($controllerLookupId = $this->iframeConfig->getControllerLookupId())) {
			$opuCtrl->forwardUrlIframeZone($this->getUrlToController(['src', $pid]),
					$this->iframeConfig->getWindowTitle());
		} else if (null !== ($viewName = $this->iframeConfig->getViewName())) {
			$uiComponent = $opuCtrl->eiu()->createView($viewName, [$this->iframeConfig->getEntryIdParamName() => $pid]);
			$opuCtrl->forwardIframeZone($uiComponent, $this->iframeConfig->isUseTemplate(),
					$this->iframeConfig->getWindowTitle());
		} else {
			$opuCtrl->forwardIframeZone(new Raw($this->iframeConfig->getSrcDoc()), $this->iframeConfig->isUseTemplate(),
					$this->iframeConfig->getWindowTitle());
		}
	}

 	function doSrc(array $params = []) {
 		$opuCtrl = OpuCtrl::from($this->cu());
 		$controller = null;
 		try {
 			$controller = $opuCtrl->eiu()->lookup($this->iframeConfig->getControllerLookupId());
 		} catch (MagicObjectUnavailableException $e) {
 			throw new InvalidEiConfigurationException('IframeEiCmd invalid configured.', 0, $e);
 		}
		
 		if (!($controller instanceof Controller)) {
 			throw new InvalidEiConfigurationException('IframeEiCmd invalid configured. '
 					. get_class($controller) . ' does not implement ' . Controller::class);
 		}
		
 		$this->delegate($controller);
 	}

	private function verifyPid(?int $pid) {
		if ($this->iframeConfig->isEntryCommand()) {
			if ($pid !== null) {
				// throws PageNotFound if pid invalid
				OpuCtrl::from($this->cu())->lookupObject($pid);
			} else {
				throw new PageNotFoundException();
			}
		} elseif ($pid !== null) {
			throw new PageNotFoundException();
		}
	}


}
