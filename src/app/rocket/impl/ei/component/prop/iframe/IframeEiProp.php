<?php
namespace rocket\impl\ei\component\prop\iframe;

use n2n\core\container\N2nContext;
use n2n\web\ui\Raw;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use rocket\ei\util\Eiu;
use rocket\ei\util\factory\EifGuiField;
use rocket\impl\ei\component\prop\adapter\DisplayableEiPropAdapter;
use rocket\si\content\impl\SiFields;

class IframeEiProp extends DisplayableEiPropAdapter {
	private $iframeConfig;

	function __construct() {
		parent::__construct();

		$this->iframeConfig = new IframeConfig();
	}

	protected function prepare() {
		$this->getConfigurator()
				->setDefaultCompatibilityLevel(CompatibilityLevel::SUITABLE)
				->addAdaption($this->iframeConfig);
	}

	function createOutEifGuiField(Eiu $eiu): EifGuiField  {
		$siField = null;
		if (null !== ($url = $this->iframeConfig->getUrl())) {
			$siField = SiFields::iframeUrlOut($url);
		} else if ($this->iframeConfig->isUseTemplate()){
			$siField = SiFields::iframeOut(new Raw($this->iframeConfig->getSrcDoc()), $eiu->getN2nContext());
		} else {
			$siField = SiFields::iframeOut(new Raw($this->iframeConfig->getSrcDoc()));
		}
		
		return $eiu->factory()->newGuiField($siField);;
	}
}