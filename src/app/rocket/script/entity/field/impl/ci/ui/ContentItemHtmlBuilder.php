<?php
namespace rocket\script\entity\field\impl\ci\model;

use n2n\ui\html\HtmlView;

class ContentItemHtmlBuilder {
	private $view;
	
	public function __construct(HtmlView $view) {
		$this->view = $view;
	}
	
	public function contentItems(array $contentItems, $panelName = null) {
		foreach ($contentItems as $contentItem) {
			if (isset($panelName) && $panelName != $contentItem->getPanel()) continue;
			$this->view->out($contentItem->createUiComponent($this->view));			
		}
	}
}