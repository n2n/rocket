<?php
namespace rocket\script\entity\field\impl\string;

use n2n\ui\Raw;
use n2n\dispatch\option\impl\StringOption;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\ManageInfo;
use n2n\ui\html\HtmlView;

class YoutubeScriptField extends AlphanumericScriptField {
	
	public function getTypeName() {
		return 'Youtube Video';
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view, 
			ManageInfo $manageInfo)  {
		$value = $scriptSelectionMapping->getValue($this->getId());
		if ($value === null) return null;
		
		$html = $view->getHtmlBuilder();
		$raw = '<iframe class="rocket-youtube-video-preview" type="text/html" src="http://www.youtube.com/embed/' . $html->getEsc($value) . '"></iframe>';
		return new Raw($raw);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\display\Editable::createOption()
	 */
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return new StringOption($this->getLabel(), null,
				$this->isRequired($scriptSelectionMapping, $manageInfo), $this->getMaxlength(), null,
				array('placeholder' => $this->getLabel()));
	}	
}
