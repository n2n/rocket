<?php
namespace rocket\si\content\impl\basic;

use rocket\si\content\SiGui;
use n2n\util\uri\Url;
use n2n\web\ui\UiComponent;

class IframeSiGui implements SiGui {
	private $url;
	private $srcDoc;
	
	function __construct(/* PHP 8 Url|UiComponent*/ $arg) {
		if ($arg instanceof Url) {
			$this->Url = $arg;
		} else {
			$this->srcDoc = $arg;
		}
	}
	
	function getTypeName(): string {
		return 'iframe';
	}

	function getData(): array {
		return [
			'url' => (string) $this->url,
			'srcDoc' => $this->srcDoc 
		];
	}

}