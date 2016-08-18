<?php

namespace rocket\script\entity\field;

use rocket\script\entity\field\impl\StatelessDisplayable;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\util\Attributes;
use rocket\script\entity\field\impl\ManageInfo;

interface StatelessEditable extends StatelessDisplayable {
	/**
	 * @return bool
	 */
	public function isReadOnly(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo);

	public function isRequired(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo);

	public function optionAttributeValueToPropertyValue(Attributes $attributes,
			ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo);

	public function propertyValueToOptionAttributeValue(ScriptSelectionMapping $scriptSelectionMapping,
			Attributes $attributes, ManageInfo $manageInfo);

	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo);
}