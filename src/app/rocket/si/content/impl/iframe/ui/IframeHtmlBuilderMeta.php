<?php
namespace rocket\si\content\impl\iframe\ui;


use n2n\impl\web\ui\view\html\HtmlView;

class IframeHtmlBuilderMeta {
	const TEMPLATE_FILE_LOCATION =  '\rocket\si\content\impl\iframe\view\iframeTemplate.html';

	private $view;

	public function __construct(HtmlView $view) {
		$this->view = $view;
	}

	/**
	 * Uses the default iframe template file defined in {@link IframeHtmlBuilderMeta::TEMPLATE_FILE_LOCATION}
	 */
	public function useTemplate() {
		$this->view->useTemplate(self::TEMPLATE_FILE_LOCATION);
	}
}
