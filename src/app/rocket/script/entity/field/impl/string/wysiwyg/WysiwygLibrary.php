<?php
namespace rocket\script\entity\field\impl\string\wysiwyg;

use n2n\ui\html\HtmlBuilder;

use n2n\ui\html\HtmlView;

use n2n\ui\html\LibraryAdapter;

class WysiwygLibrary extends LibraryAdapter {
	public function apply(HtmlView $view, HtmlBuilder $html) {
		$html->addJs('js/thirdparty/ckeditor/ckeditor.js', 'rocket');
	}
}