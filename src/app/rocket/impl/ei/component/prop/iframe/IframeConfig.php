<?php
namespace rocket\impl\ei\component\prop\iframe;

use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\persistence\meta\structure\Column;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;

class IframeConfig extends PropConfigAdaption {
	const ATTR_SRC_DOC_KEY = 'srcDoc';
	const ATTR_USE_TEMPLATE_KEY = 'useTemplate';

	private $srcDoc;
	private $useTemplate = true;

	function setup(Eiu $eiu, DataSet $dataSet) {
		if ($dataSet->contains(self::ATTR_SRC_DOC_KEY)) {
			$this->setSrcDoc($dataSet->reqString(self::ATTR_SRC_DOC_KEY));
		}

		if ($dataSet->contains(self::ATTR_USE_TEMPLATE_KEY)) {
			$this->setSrcDoc($dataSet->reqBool(self::ATTR_USE_TEMPLATE_KEY));
		}
	}

	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		$dataSet->set(self::ATTR_USE_TEMPLATE_KEY, true);
	}

	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$magCollection->addMag(self::ATTR_SRC_DOC_KEY, new StringMag('Source Document',
			$dataSet->optString(self::ATTR_SRC_DOC_KEY, $this->getSrcDoc())));

		$magCollection->addMag(self::ATTR_USE_TEMPLATE_KEY, new BoolMag('Use Template',
			$dataSet->optBool(self::ATTR_USE_TEMPLATE_KEY, $this->isUseTemplate())));
	}

	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$srcDocMag = $magCollection->getMagByPropertyName(self::ATTR_SRC_DOC_KEY);
		$useTemplateMag = $magCollection->getMagByPropertyName(self::ATTR_USE_TEMPLATE_KEY);

		$dataSet->set(self::ATTR_SRC_DOC_KEY, $srcDocMag->getValue());
		$dataSet->set(self::ATTR_USE_TEMPLATE_KEY, $useTemplateMag->getValue());
	}

	function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		return null;
	}

	function assignProperty(PropertyAssignation $propertyAssignation) {
		// TODO: Implement assignProperty() method.
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
	public function setSrcDoc(string $srcDoc): void {
		$this->srcDoc = $srcDoc;
	}

	/**
	 * @return boolean
	 */
	public function isUseTemplate() {
		return $this->useTemplate;
	}

	/**
	 * @param boolean $useTemplate
	 */
	public function setUseTemplate(bool $useTemplate): void {
		$this->useTemplate = $useTemplate;
	}
}