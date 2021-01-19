<?php
namespace rocket\si\content\impl;

use n2n\util\type\attrs\DataSet;
use n2n\web\ui\UiComponent;

class IframeInSiField extends InSiFieldAdapter {
	private UiComponent $uiComponent;
	private bool $useTemplate;
	private array $params;

	function __construct(string $srcDoc, bool $useTemplate) {
		$this->uiComponent = $srcDoc;
		$this->useTemplate = $useTemplate;
	}

	/**
	 * @return UiComponent|string
	 */
	public function getUiComponent() {
		return $this->uiComponent;
	}

	/**
	 * @param UiComponent|string $uiComponent
	 */
	public function setUiComponent($uiComponent): void {
		$this->uiComponent = $uiComponent;
	}

	/**
	 * @return bool
	 */
	public function isUseTemplate(): bool {
		return $this->useTemplate;
	}

	/**
	 * @param bool $useTemplate
	 */
	public function setUseTemplate(bool $useTemplate): void {
		$this->useTemplate = $useTemplate;
	}

	/**
	 * @return array
	 */
	public function getParams(): array {
		return $this->params;
	}

	/**
	 * @param array $params
	 */
	public function setParams(array $params): void {
		$this->params = $params;
	}

	function getType(): string {
		// TODO: Implement getType() method.
	}

	function getData(): array {
		// TODO: Implement getData() method.
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$this->useTemplate = (new DataSet($data))->reqString('useTemplate', true);
	}
}