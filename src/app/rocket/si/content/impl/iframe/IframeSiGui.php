<?php
namespace rocket\si\content\impl\basic;

use rocket\si\content\SiGui;

class IframeSiGui implements SiGui {
	
	private $url;
	
	function __construct() {
		
	}
	
	function getTypeName(): string {
		return 'iframe';
	}

	function getData(): array {
		return [
			'src' => (string) $this->url
		];
	}

}