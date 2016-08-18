<?php
namespace rocket\script\entity\manage\display;

use n2n\ui\html\HtmlView;
use rocket\script\entity\manage\model\EntryModel;

interface Displayable {
	
	public function getDisplayLabel();
	
	public function getHtmlContainerAttrs(EntryModel $entryModel);
	
	public function createUiOutputField(EntryModel $entryModel, HtmlView $view);
}