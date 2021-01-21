<?php
namespace rocket\impl\ei\component\command\iframe\config;

use n2n\context\Lookupable;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\config\ConfigAdaption;
use n2n\util\uri\Url;

class IframeConfig extends ConfigAdaption {
	const ATTR_URL_KEY = 'url';
	const ATTR_SRC_DOC_KEY = 'srcDoc';
	const ATTR_USE_TEMPLATE_KEY = 'useTemplate';

	private $url;
	private $srcDoc;
	private $useTemplate = true;
	private $buttonIcon;
	private $buttonLabel;
	private $buttonTooltip;

	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$magCollection->addMag(self::ATTR_URL_KEY, new StringMag('Source URL',
			$dataSet->optString(self::ATTR_URL_KEY, $this->getUrl())));

		$magCollection->addMag(self::ATTR_SRC_DOC_KEY, new StringMag('Source Document',
				$dataSet->optString(self::ATTR_SRC_DOC_KEY, $this->getSrcDoc())));

		$magCollection->addMag(self::ATTR_USE_TEMPLATE_KEY, new BoolMag('Use Template',
				$dataSet->optBool(self::ATTR_USE_TEMPLATE_KEY, $this->isUseTemplate())));
	}

	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$urlMag = $magCollection->getMagByPropertyName(self::ATTR_URL_KEY);
		$srcDocMag = $magCollection->getMagByPropertyName(self::ATTR_SRC_DOC_KEY);
		$useTemplateMag = $magCollection->getMagByPropertyName(self::ATTR_USE_TEMPLATE_KEY);

		$dataSet->set(self::ATTR_URL_KEY, $urlMag);
		$dataSet->set(self::ATTR_SRC_DOC_KEY, $srcDocMag->getValue());
		$dataSet->set(self::ATTR_USE_TEMPLATE_KEY, $useTemplateMag->getValue());
	}

	function setup(Eiu $eiu, DataSet $dataSet) {
		$this->setUrl(Url::build($dataSet->optString(self::ATTR_URL_KEY)));
		$this->setSrcDoc($dataSet->optString(self::ATTR_SRC_DOC_KEY));
		$this->setUseTemplate($dataSet->optBool(self::ATTR_USE_TEMPLATE_KEY, true));
	}

	/**
	 * @return Url|null
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param Url|null $url
	 */
	public function setUrl(?Url $url) {
		$this->url = $url;
	}

	/**
	 * @return string|null
	 */
	public function getSrcDoc() {
		return $this->srcDoc;
	}

	/**
	 * @param string|null $srcDoc
	 */
	public function setSrcDoc(?string $srcDoc) {
		$this->srcDoc = $srcDoc;
	}

	/**
	 * @return bool
	 */
	public function isUseTemplate() {
		return $this->useTemplate;
	}

	/**
	 * @param bool $useTemplate
	 */
	public function setUseTemplate(bool $useTemplate) {
		$this->useTemplate = $useTemplate;
	}

	/**
	 * @return string|null
	 */
	public function getButtonIcon() {
		return $this->buttonIcon;
	}

	/**
	 * @param string|null $buttonIcon
	 */
	public function setButtonIcon(?string $buttonIcon) {
		$this->buttonIcon = $buttonIcon;
	}

	/**
	 * @return string|null
	 */
	public function getButtonLabel() {
		return $this->buttonLabel;
	}

	/**
	 * @param string|null $buttonLabel
	 */
	public function setButtonLabel(?string $buttonLabel) {
		$this->buttonLabel = $buttonLabel;
	}

	/**
	 * @return string|null
	 */
	public function getButtonTooltip() {
		return $this->buttonTooltip;
	}

	/**
	 * @param string|null $buttonTooltip
	 */
	public function setButtonTooltip(?string $buttonTooltip) {
		$this->buttonTooltip = $buttonTooltip;
	}
}