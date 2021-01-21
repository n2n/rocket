<?php
namespace rocket\si\content\impl;

use n2n\util\type\attrs\DataSet;
use rocket\si\content\impl\iframe\IframeData;
use n2n\util\type\ArgUtils;

class IframeInSiField extends InSiFieldAdapter {
	private $iframeData;
	private array $params;
	
	public function __construct(IframeData $iframeData) {
		$this->iframeData = $iframeData;
	}
	
	/**
	 * @return string
	 */
	function getType(): string {
		return 'iframe-in';
	}

	/**
	 * @return array
	 */
	function getParams(): array {
		return $this->params;
	}

	/**
	 * @param array $params
	 */
	function setParams(array $params): void {
		ArgUtils::valArray($params, ['scalar', null]);
		$this->params = $params;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		$data = $this->iframeData->toArray();
		$data['params'] = $this->getParams();
		return $data;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$ds = new DataSet($data);
		$this->data = $ds->reqScalarArray('params');
	}
}