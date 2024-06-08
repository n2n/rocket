<?php
namespace rocket\ui\si\content\impl\iframe;

use rocket\ui\si\content\SiGui;

class IframeSiGui implements SiGui {
	private $iframeData;
	
	public function __construct(IframeData $iframeData) {
		$this->iframeData = $iframeData;
	}
	
	function getTypeName(): string {
		return 'iframe';
	}

	function getData(): array {
		return $this->iframeData->toArray();
	}

}
