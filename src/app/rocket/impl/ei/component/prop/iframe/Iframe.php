<?php
namespace rocket\impl\ei\component\prop\iframe;

use n2n\web\http\nav\Murl;
use n2n\web\ui\Raw;
use rocket\op\ei\component\prop\indepenent\CompatibilityLevel;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\util\factory\EifGuiField;
use rocket\impl\ei\component\prop\adapter\DisplayConfigTrait;
use rocket\ui\si\content\impl\SiFields;

class Iframe extends DisplayConfigTrait {
	private IframeConfig $iframeConfig;

	function __construct() {
		parent::__construct();

		$this->iframeConfig = new IframeConfig();
	}

	protected function prepare() {
		$this->getConfigurator()
				->addAdaption($this->iframeConfig);
	}

	function buildOutGuiField(Eiu $eiu): ?BackableGuiField  {
		$siField = null;

		if (null !== $this->iframeConfig->getControllerLookupId()) {
			$murlController = Murl::controller($eiu->lookup($this->iframeConfig->getControllerLookupId()));
			$siField = SiFields::iframeUrlOut($murlController->toUrl($eiu->getN2nContext()));
		} else if (null !== ($url = $this->iframeConfig->getUrl())) {
			$siField = SiFields::iframeUrlOut($url);
		} else if ($this->iframeConfig->isUseTemplate()){
			$siField = SiFields::iframeOut(new Raw($this->iframeConfig->getSrcDoc()), $eiu->getN2nContext());
		} else {
			$siField = SiFields::iframeOut(new Raw($this->iframeConfig->getSrcDoc()));
		}
		
		return $eiu->factory()->newGuiField($siField);
	}
}
