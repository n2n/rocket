<?php
namespace rocket\si\content\impl\iframe;


use n2n\core\container\N2nContext;
use n2n\web\ui\UiComponent;
use n2n\web\ui\view\View;
use n2n\web\ui\ViewFactory;
use rocket\si\content\impl\OutSiFieldAdapter;

class IframeOutSiField extends OutSiFieldAdapter {
	private UiComponent $uiComponent;
	private bool $useTemplate = true;
	private N2nContext $n2nContext;

	public function __construct(N2nContext $n2nContext, UiComponent $uiComponent, bool $useTemplate) {
		$this->n2nContext = $n2nContext;
		$this->uiComponent = $uiComponent;
		$this->useTemplate = $useTemplate;
	}

	/**
	 * @return UiComponent
	 */
	public function getUiComponent(): UiComponent {
		return $this->uiComponent;
	}

	/**
	 * @param UiComponent $uiComponent
	 * @return IframeOutSiField
	 */
	public function setUiComponent(UiComponent $uiComponent) {
		$this->uiComponent = $uiComponent;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isUseTemplate(): bool {
		return $this->useTemplate;
	}

	/**
	 * @param bool $useTemplate
	 * @return IframeOutSiField
	 */
	public function setUseTemplate(bool $useTemplate) {
		$this->useTemplate = $useTemplate;
		return $this;
	}

	/**
	 * @return string
	 */
	function getType(): string {
		return 'iframe-out';
	}

	/**
	 * @return array
	 * @throws \n2n\util\magic\MagicObjectUnavailableException
	 */
	function getData(): array {
		/**
		 * @var ViewFactory
		 */
		$viewFactory = $this->n2nContext->lookup(ViewFactory::class);
		/**
		 * @var View
		 */
		$view = $viewFactory->create('rocket\si\content\impl\iframe\view\iframeContent.html', array('useTemplate' => $this->useTemplate));
		return [
			'value' => $view->getContents()
		];
	}
}