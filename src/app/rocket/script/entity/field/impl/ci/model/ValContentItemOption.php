<?php

namespace rocket\script\entity\field\impl\ci\model;

use n2n\dispatch\PropertyPathPart;
use n2n\dispatch\map\BindingErrors;
use n2n\core\MessageCode;
use n2n\dispatch\val\SimplePropertyValidator;
use n2n\dispatch\PropertyPath;

class ValContentItemOption extends SimplePropertyValidator {
	private $panelConfigs;
	
	public function __construct(array $panelConfigs) {
		$this->panelConfigs = $panelConfigs;
	}
	/* (non-PHPdoc)
	 * @see \n2n\dispatch\val\SimplePropertyValidator::validateValue()
	 */
	protected function validateValue($value, PropertyPathPart $pathPart, BindingErrors $bindingErrors) {
		return;	
		foreach ($this->panelConfigs as $panelConfig) {
			if (!$panelConfig->isRestricted()) continue;

			$panelName = $panelConfig->getName();
			$allowedContentItemIds = $panelConfig->getAllowedContentItemIds();
			$propertyPath = new PropertyPath(array($pathPart));
			foreach ($value->currentEntryForms as $key => $entryFormMappingResult) {
				if (!$entryFormMappingResult->mainEntryFormPart->optionForm->has('panel')
						|| $entryFormMappingResult->mainEntryFormPart->optionForm->panel != $panelName) continue;
				$this->checkTypeId(
						$propertyPath->ext('currentEntryForms[' . $key . ']')->ext('selectedTypeId'), 
						$entryFormMappingResult->selectedTypeId, $allowedContentItemIds, $bindingErrors);
			}

			foreach ($value->newEntryForms as $key => $entryFormMappingResult) {
				if (!$entryFormMappingResult->mainEntryFormPart->optionForm->has('panel')
						|| $entryFormMappingResult->mainEntryFormPart->optionForm->panel != $panelName) continue;
				$this->checkTypeId(
						$propertyPath->ext('newEntryForms[' . $key . ']')->ext('selectedTypeId'),
						$entryFormMappingResult->selectedTypeId, $allowedContentItemIds, $bindingErrors);
			}
			
		}		
	}
	
	private function checkTypeId($propertyExpression, $selectedTypeId, array $allowedContentItemIds, 
			BindingErrors $be) {
		if (in_array($selectedTypeId, $allowedContentItemIds)) return;
		$be->addError($propertyExpression, new MessageCode('script_field_contentitem_invalid_panel'));
	}
}