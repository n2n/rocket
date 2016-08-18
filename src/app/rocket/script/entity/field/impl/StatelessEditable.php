<?php

namespace rocket\script\entity\field\impl;

use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\util\Attributes;

interface StatelessEditable extends StatelessDisplayable {
	
	public function isReadOnly(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo);
	
	public function isRequired(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo);
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo);
	
	public function optionAttributeValueToPropertyValue(Attributes $attributes, 
			ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo);
		
	public function propertyValueToOptionAttributeValue(ScriptSelectionMapping $scriptSelectionMapping, 
			Attributes $attributes, ManageInfo $manageInfo);
}