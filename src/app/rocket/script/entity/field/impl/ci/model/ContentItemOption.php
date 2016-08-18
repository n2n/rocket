<?php
namespace rocket\script\entity\field\impl\ci\model;

use n2n\dispatch\option\impl\OptionAdapter;
use n2n\dispatch\map\BindingConstraints;
use n2n\ui\html\HtmlView;
use n2n\dispatch\PropertyPath;
use n2n\dispatch\DispatchableTypeAnalyzer;
use rocket\script\entity\field\impl\relation\option\ToManyOption;
use n2n\core\DynamicTextCollection;

class ContentItemOption extends OptionAdapter {
	private $toManyOption;
	private $panelConfigs;
	private $frozen;

	public function __construct(ToManyOption $toManyOption, array $panelConfigs, $frozen = false) {
		parent::__construct($toManyOption->getLabel(), $toManyOption->getLabel(), $toManyOption->isRequired());
		$this->toManyOption = $toManyOption;
		$this->panelConfigs = $panelConfigs;
		$this->frozen = (bool) $frozen;
	}

	public function getContainerAttrs() {
	 	$panelDataAttrs = array();
		foreach ($this->panelConfigs as $panelConfig) {
			$panelDataAttrs[$panelConfig->getName()] = array('label' => $panelConfig->getLabel(),
					'allowedContentItemIds' => $panelConfig->getAllowedContentItemIds());
		}
		$dtc = new DynamicTextCollection('rocket');
		return array('class' => 'rocket-content-item-option', 'data-content-item-panels' => json_encode($panelDataAttrs), 
				'data-text-up' => $dtc->translate('Up'), 'data-text-down' => $dtc->translate('Down'), 
				'data-frozen' => json_encode($this->frozen));
	}
	
	public function createManagedPropertyType($propertyName, DispatchableTypeAnalyzer $dispatchableTypeAnalyzer) {
		return $this->toManyOption->createManagedPropertyType($propertyName, $dispatchableTypeAnalyzer);
	}

	public function applyValidation($propertyName, BindingConstraints $bc) {
		$this->toManyOption->applyValidation($propertyName, $bc);
		$bc->val($propertyName, new ValContentItemOption($this->panelConfigs));
	}
	
	public function attributeValueToOptionValue($attributeValue) {
		return $this->toManyOption->attributeValueToOptionValue($attributeValue);
	}
	
	public function optionValueToAttributeValue($optionValue) {
		return $this->toManyOption->optionValueToAttributeValue($optionValue);
	}

	public function createUiField(PropertyPath $propertyPath, HtmlView $view) {
		return $this->toManyOption->createUiField($propertyPath, $view);
	}
	
	public function getToManyOption() {
		return $this->toManyOption;
	}

	public function setToManyOption($toManyOption) {
		$this->toManyOption = $toManyOption;
	}

	public function getPanelConfigs() {
		return $this->panelConfigs;
	}

	public function setPanelConfigs($panelConfigs) {
		$this->panelConfigs = $panelConfigs;
	}
}