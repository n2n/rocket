<?php
namespace rocket\script\entity\manage\display;

use n2n\util\Attributes;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\model\EntryModel;

interface Editable extends Displayable {
	public function isRequired(EntryModel $entryModel);
	public function isReadOnly(EntryModel $entryModel);
	public function createOption(EntryModel $entryModel);
	public function propertyValueToOptionAttributeValue(ScriptSelectionMapping $mapping, 
			Attributes $attributes, EntryModel $entryModel);
	public function optionAttributeValueToPropertyValue(Attributes $attributes, 
			ScriptSelectionMapping $mapping, EntryModel $entryModel);
}