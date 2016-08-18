<?php

namespace rocket\script\entity\field\impl;

use rocket\script\entity\manage\display\Displayable;
use rocket\script\entity\manage\ScriptState;
use n2n\ui\html\HtmlView;
use rocket\script\entity\manage\model\EntryModel;
use n2n\util\Attributes;

class StatelessDisplayableDecorator implements Displayable {
	private $statelessDisplayable;
	private $scriptState;
	private $maskAttributes;	
	
	public function __construct(StatelessDisplayable $statelessDisplayable, ScriptState $scriptState, 
			Attributes $maskAttributes) {
		$this->statelessDisplayable = $statelessDisplayable;
		$this->scriptState = $scriptState;
		$this->maskAttributes = $maskAttributes;
	} 

	public function getDisplayLabel() {
		return $this->statelessDisplayable->getDisplayLabel(new ManageInfo($this->scriptState, $this->maskAttributes));
	}

	public function getHtmlContainerAttrs(EntryModel $entryModel) {
		return $this->statelessDisplayable->getHtmlContainerAttrs($entryModel->getScriptSelectionMapping(), 
				new ManageInfo($this->scriptState, $this->maskAttributes, $entryModel));
	}
	
	public function createUiOutputField(EntryModel $entryModel, HtmlView $htmlView) {
		return $this->statelessDisplayable->createUiOutputField($entryModel->getScriptSelectionMapping(),
				$htmlView, new ManageInfo($this->scriptState, $this->maskAttributes, $entryModel));
	}	
}