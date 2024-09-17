<?php
namespace rocket\ui\si\content\impl\iframe;

use n2n\util\type\attrs\DataSet;
use n2n\util\type\ArgUtils;
use rocket\ui\si\content\impl\InSiFieldAdapter;

class IframeInSiField extends InSiFieldAdapter {
	private $iframeData;
	private $params = [];
	
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
		ArgUtils::valArray($params, ['scalar', 'null']);
		$this->params = $params;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		$data = [...parent::toJsonStruct(), ...$this->iframeData->toArray()];
		$data['params'] = $this->getParams();
//		$data['messages'] = $this->getMessageStrs();
		return $data;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::handleInput()
	 */
	function handleInputValue(array $data): bool {
		$ds = new DataSet($data);
		$this->params = $ds->reqScalarArray('params', false, true);
		return true;
	}
}
