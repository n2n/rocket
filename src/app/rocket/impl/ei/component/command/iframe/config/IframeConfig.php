<?php
namespace rocket\impl\ei\component\command\iframe\config;

use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\config\EiConfiguratorAdaption;

class IframeConfig implements EiConfiguratorAdaption {
	const ATTR_URL = 'url';
	const ATTR_SRC_DOC_KEY = 'srcDoc';
	const ATTR_USE_TEMPLATE_KEY = 'useTemplate';

	private $url;
	private $srcDoc;
	private $useTemplate = true;
	private $buttonIcon;
	private $buttonLabel;
	private $buttonTooltip;

	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {


		$magCollection->addMag(self::ATTR_URL, new StringMag('Source URL',
			$dataSet->optString(self::ATTR_URL, $this->getUrl())));

		$magCollection->addMag(self::ATTR_SRC_DOC_KEY, new StringMag('Source Document',
				$dataSet->optString(self::ATTR_SRC_DOC_KEY, $this->getSrcDoc())));

		$magCollection->addMag(self::ATTR_USE_TEMPLATE_KEY, new BoolMag('Use Template',
				$dataSet->optBool(self::ATTR_USE_TEMPLATE_KEY, $this->isUseTemplate())));
	}

	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$urlMag = $magCollection->getMagByPropertyName(self::ATTR_URL);
		$srcDocMag = $magCollection->getMagByPropertyName(self::ATTR_SRC_DOC_KEY);
		$useTemplateMag = $magCollection->getMagByPropertyName(self::ATTR_USE_TEMPLATE_KEY);

		$dataSet->set(self::ATTR_URL, $urlMag);
		$dataSet->set(self::ATTR_SRC_DOC_KEY, $srcDocMag->getValue());
		$dataSet->set(self::ATTR_USE_TEMPLATE_KEY, $useTemplateMag->getValue());
	}

	function setup(Eiu $eiu, DataSet $dataSet) {
		if ($dataSet->contains(self::ATTR_URL)) {
			$this->setSrcDoc($dataSet->reqString(self::ATTR_URL));
		}

		if ($dataSet->contains(self::ATTR_SRC_DOC_KEY)) {
			$this->setSrcDoc($dataSet->reqString(self::ATTR_SRC_DOC_KEY));
		}

		if ($dataSet->contains(self::ATTR_USE_TEMPLATE_KEY)) {
			$this->setSrcDoc($dataSet->reqBool(self::ATTR_USE_TEMPLATE_KEY));
		}
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param string $url
	 */
	public function setUrl(string $url) {
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getSrcDoc() {
		return $this->srcDoc;
	}

	/**
	 * @param string $srcDoc
	 */
	public function setSrcDoc(string $srcDoc) {
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
	 * @return string
	 */
	public function getButtonIcon() {
		return $this->buttonIcon;
	}

	/**
	 * @param string $buttonIcon
	 */
	public function setButtonIcon(string $buttonIcon): void {
		$this->buttonIcon = $buttonIcon;
	}

	/**
	 * @return string
	 */
	public function getButtonLabel() {
		return $this->buttonLabel;
	}

	/**
	 * @param string $buttonLabel
	 */
	public function setButtonLabel(string $buttonLabel) {
		$this->buttonLabel = $buttonLabel;
	}

	/**
	 * @return string
	 */
	public function getButtonTooltip() {
		return $this->buttonTooltip;
	}

	/**
	 * @param string $buttonTooltip
	 */
	public function setButtonTooltip(string $buttonTooltip) {
		$this->buttonTooltip = $buttonTooltip;
	}
}