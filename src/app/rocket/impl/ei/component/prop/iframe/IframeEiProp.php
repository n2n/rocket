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
		$n2nContext = $eiu->getN2nContext();

		return $eiu->factory()->newGuiField(SiFields::iframeOut($n2nContext,
				new Raw($this->iframeConfig->getSrcDoc()), $this->iframeConfig->isUseTemplate()));
	}

	function createInEifGuiField(Eiu $eiu): EifGuiField {
		$addonConfig = $this->getAddonConfig();

		$siField = SiFields::iframeIn($eiu->getN2nContext(), $this->iframeConfig->getSrcDoc(), $this->iframeConfig->isUseTemplate());

		return $eiu->factory()->newGuiField($siField)
			->setSaver(function () use ($siField, $eiu) {
				$eiu->field()->setValue($siField->getValue());
			});
	}
}