<?php

namespace rocket\script\entity\field\impl;

use rocket\script\entity\manage\ScriptState;
use n2n\util\Attributes;
use rocket\script\entity\manage\display\Editable;
use rocket\script\entity\field\StatelessEditable;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\model\EntryModel;
use n2n\reflection\ArgumentUtils;

class StatelessEditableDecorator extends StatelessDisplayableDecorator implements Editable {
	private $statelessEditable;
	private $scriptState;
	private $maskAttributes;
	
	public function __construct(StatelessEditable $statelessEditable, ScriptState $scriptState, 
			Attributes $maskAttributes) {
		parent::__construct($statelessEditable, $scriptState, $maskAttributes);
		$this->statelessEditable = $statelessEditable;
		$this->scriptState = $scriptState;
		$this->maskAttributes = $maskAttributes;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\display\Editable::isRequired()
	 */
	public function isRequired(EntryModel $entryModel) {
		return $this->statelessEditable->isRequired($entryModel->getScriptSelectionMapping(), 
				new ManageInfo($this->scriptState, $this->maskAttributes, $entryModel));
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\display\Editable::isReadOnly()
	 */
	public function isReadOnly(EntryModel $entryModel) {
		return $this->statelessEditable->isReadOnly($entryModel->getScriptSelectionMapping(), 
				new ManageInfo($this->scriptState, $this->maskAttributes, $entryModel));
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\display\Editable::createOption()
	 */
	public function createOption(EntryModel $entryModel) {
		$option = $this->statelessEditable->createOption($entryModel->getScriptSelectionMapping(), 
				new ManageInfo($this->scriptState, $this->maskAttributes, $entryModel));
		ArgumentUtils::validateReturnType($option, 'n2n\dispatch\option\Option', $this->statelessEditable, 'createOption');
		return $option;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\display\Editable::propertyValueToOptionAttributeValue()
	 */
	public function propertyValueToOptionAttributeValue(ScriptSelectionMapping $scriptSelectionMapping, 
			Attributes $attributes, EntryModel $entryModel) {
		return $this->statelessEditable->propertyValueToOptionAttributeValue($scriptSelectionMapping, $attributes, 
				new ManageInfo($this->scriptState, $this->maskAttributes, $entryModel));
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\display\Editable::optionAttributeValueToPropertyValue()
	 */
	public function optionAttributeValueToPropertyValue(Attributes $attributes, 
			ScriptSelectionMapping $scriptSelectionMapping, EntryModel $entryModel) {
		return $this->statelessEditable->optionAttributeValueToPropertyValue($attributes, $scriptSelectionMapping, 
				new ManageInfo($this->scriptState, $this->maskAttributes, $entryModel));
	}	
}