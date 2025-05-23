<?php
namespace rocket\ui\si\content\impl\iframe;

use rocket\ui\si\content\impl\OutSiFieldAdapter;

class IframeOutSiField extends OutSiFieldAdapter {
	private $iframeData;
	
	public function __construct(IframeData $iframeData) {
		$this->iframeData = $iframeData;
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
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			...$this->iframeData->toArray(),
			...parent::toJsonStruct($n2nContext)
		];
	}
}