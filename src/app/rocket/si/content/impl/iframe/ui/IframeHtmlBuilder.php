<?php
namespace rocket\si\content\impl\iframe\ui;

use n2n\impl\web\ui\view\html\HtmlView;

class IframeHtmlBuilder {

	private $view;
	private $meta;

	/**
	 * @param HtmlView $view
	 */
	public function __construct(HtmlView $view) {
		$this->view = $view;
		$this->meta = new IframeHtmlBuilderMeta($view);
	}

	/**
	 * @return IframeHtmlBuilderMeta
	 */
	public function meta(): IframeHtmlBuilderMeta {
		return $this->meta;
	}
}
