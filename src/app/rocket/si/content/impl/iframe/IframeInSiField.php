<?php
namespace rocket\si\content\impl\iframe;

use n2n\util\type\attrs\DataSet;
use n2n\util\type\ArgUtils;
use rocket\si\content\impl\InSiFieldAdapter;

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
	 * @return IframeInSiField
	 */
	function setParams(array $params) {
		ArgUtils::valArray($params, ['scalar', null]);
		$this->params = $params;
		return $this;
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