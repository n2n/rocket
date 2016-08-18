<?php
namespace rocket\script\entity\field\impl\string\wysiwyg;

use n2n\ui\html\HtmlView;

class WysiwygCssConfigurationExample implements WysiwygCssConfiguration {
	public function getContentCssPaths(HtmlView $view) {
		return array('assets/rocket/css/style.css', 'assets/rocket/css/style2.css');
	}
	
	public function getBodyClass() {
		return 'rocket-wysiwyg-test';
	}
	
	public function getBodyId() {
		return 'rocket-wysiwyg-test';
	}

	public function getAdditionalStyles() {
		return array(new WysiwygStyle('Attention Box', 'div', array('class' => 'box-attention')));
	}
	
	public function getFormatTags() {
		return array("address");
	}

}