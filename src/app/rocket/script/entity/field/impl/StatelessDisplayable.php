<?php

namespace rocket\script\entity\field\impl;

use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\ui\html\HtmlView;

interface StatelessDisplayable {
	public function getDisplayLabel(ManageInfo $manageInfo);
	
	public function getHtmlContainerAttrs(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo);
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, 
			HtmlView $htmlView, ManageInfo $manageInfo);
}