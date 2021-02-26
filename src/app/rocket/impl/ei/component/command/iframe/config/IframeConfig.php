<?php
namespace rocket\impl\ei\component\command\iframe\config;

use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\config\ConfigAdaption;
use n2n\util\uri\Url;

class IframeConfig extends ConfigAdaption {
	const ATTR_URL_KEY = 'url';
	const ATTR_CONTROLLER_LOOKUP_ID_KEY = 'controllerLookupId';
	const ATTR_SRC_DOC_KEY = 'srcDoc';
	const ATTR_USE_TEMPLATE_KEY = 'useTemplate';
	const ATTR_VIEW_NAME_KEY = 'viewName';
	const ATTR_USE_ENTRY_COMMAND_KEY = 'entryCommand';

	private $url;
	private $controllerLookupId;
	private $srcDoc;
	private $viewName;
	private $useTemplate = true;
	private $entryCommand = true;
	private $entryIdParamName = 'id';
	private $buttonIcon;
	private $buttonLabel;
	private $buttonTooltip;

	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$magCollection->addMag(self::ATTR_URL_KEY, new StringMag('Source URL',
			$dataSet->optString(self::ATTR_URL_KEY, $this->getUrl())));

		$magCollection->addMag(self::ATTR_CONTROLLER_LOOKUP_ID_KEY, new StringMag('Controller Lookup Id',
			$dataSet->optString(self::ATTR_CONTROLLER_LOOKUP_ID_KEY, $this->getControllerLookupId())));

		$magCollection->addMag(self::ATTR_SRC_DOC_KEY, new StringMag('Source Document',
				$dataSet->optString(self::ATTR_SRC_DOC_KEY, $this->getSrcDoc())));

		$magCollection->addMag(self::ATTR_VIEW_NAME_KEY, new StringMag('View Name',
			$dataSet->optString(self::ATTR_VIEW_NAME_KEY, $this->getViewName())));

		$magCollection->addMag(self::ATTR_USE_TEMPLATE_KEY, new BoolMag('Use Template',
				$dataSet->optBool(self::ATTR_USE_TEMPLATE_KEY, $this->isUseTemplate())));

		$magCollection->addMag(self::ATTR_USE_ENTRY_COMMAND_KEY, new BoolMag('Entry Command',
			$dataSet->optBool(self::ATTR_USE_ENTRY_COMMAND_KEY, $this->isEntryCommand())));
	}

	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$urlMag = $magCollection->getMagByPropertyName(self::ATTR_URL_KEY);
		$controllerLookupIdMag = $magCollection->getMagByPropertyName(self::ATTR_CONTROLLER_LOOKUP_ID_KEY);
		$srcDocMag = $magCollection->getMagByPropertyName(self::ATTR_SRC_DOC_KEY);
		$viewNameMag = $magCollection->getMagByPropertyName(self::ATTR_VIEW_NAME_KEY);
		$useTemplateMag = $magCollection->getMagByPropertyName(self::ATTR_USE_TEMPLATE_KEY);
		$entryCommand = $magCollection->getMagByPropertyName(self::ATTR_USE_ENTRY_COMMAND_KEY);

		$dataSet->set(self::ATTR_URL_KEY, $urlMag->getValue());
		$dataSet->set(self::ATTR_CONTROLLER_LOOKUP_ID_KEY, $controllerLookupIdMag->getValue());
		$dataSet->set(self::ATTR_SRC_DOC_KEY, $srcDocMag->getValue());
		$dataSet->set(self::ATTR_VIEW_NAME_KEY, $viewNameMag->getValue());
		$dataSet->set(self::ATTR_USE_TEMPLATE_KEY, $useTemplateMag->getValue());
		$dataSet->set(self::ATTR_USE_ENTRY_COMMAND_KEY, $entryCommand->getValue());
	}

	function setup(Eiu $eiu, DataSet $dataSet) {
		$this->setUrl(Url::build($dataSet->optString(self::ATTR_URL_KEY)));
		$this->setControllerLookupId($dataSet->optString(self::ATTR_CONTROLLER_LOOKUP_ID_KEY));
		$this->setSrcDoc($dataSet->optString(self::ATTR_SRC_DOC_KEY));
		$this->setViewName($dataSet->optString(self::ATTR_VIEW_NAME_KEY));
		$this->setUseTemplate($dataSet->optBool(self::ATTR_USE_TEMPLATE_KEY, $this->useTemplate));
		$this->setEntryCommand($dataSet->optBool(self::ATTR_USE_ENTRY_COMMAND_KEY, $this->entryCommand));
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
	 * @return string
	 */
	public function getControllerLookupId() {
		return $this->controllerLookupId;
	}

	/**
	 * @param string $controllerLookupId
	 */
	public function setControllerLookupId(?string $controllerLookupId): void {
		$this->controllerLookupId = $controllerLookupId;
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

	/**
	 * @return string
	 */
	public function getViewName() {
		return $this->viewName;
	}

	/**
	 * @param string $viewName
	 */
	public function setViewName(?string $viewName) {
		$this->viewName = $viewName;
	}

	/**
	 * @return bool
	 */
	public function isEntryCommand() {
		return $this->entryCommand;
	}

	/**
	 * @param bool $entryCommand
	 */
	public function setEntryCommand(bool $entryCommand) {
		$this->entryCommand = $entryCommand;
	}

	/**
	 * @return string
	 */
	public function getEntryIdParamName() {
		return $this->entryIdParamName;
	}

	/**
	 * @param string $entryIdParamName
	 */
	public function setEntryIdParamName(string $entryIdParamName) {
		$this->entryIdParamName = $entryIdParamName;
	}
}

