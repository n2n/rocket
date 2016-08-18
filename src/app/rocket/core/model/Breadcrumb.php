<?php
namespace rocket\core\model;

class Breadcrumb {
	const TYPE_DRAFT = 'draft';
	const TYPE_TRANSLATION = 'translation';
	
	private $url;
	private $label;
	private $type;
	
	public function __construct($url, $label) {
		$this->url = $url;
		$this->label = $label;
	}
	
	public function getUrl() {
		return $this->url;
	}
	
	public function setUrl($url) {
		$this->url = $url;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function setType($type) {
		$this->type = (string) $type;
	}
}