<?php
namespace rocket\ui\si\content\impl\iframe;

use rocket\ui\si\content\SiGui;
use n2n\core\container\N2nContext;
use rocket\ui\si\api\response\SiInputResult;
use rocket\ui\si\api\request\SiInput;

class IframeSiGui implements SiGui {

	public function __construct(private IframeData $iframeData) {
	}
	
	function getTypeName(): string {
		return 'iframe';
	}

	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return $this->iframeData->toArray();
	}

	function handleSiInput(SiInput $siInput, N2nContext $n2nContext): SiInputResult {
		// TODO: Implement handleSiInput() method.
	}
}
