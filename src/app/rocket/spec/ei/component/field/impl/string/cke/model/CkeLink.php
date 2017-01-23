<?php
namespace rocket\spec\ei\component\field\impl\string\cke\model;

use n2n\util\uri\Url;

class CkeLink {
	private $label;
	private $url;
	
	/**
	 * @param string $label
	 * @param Url|string $url
	 */
	public function __construct(string $label, $url) {
		$this->label = $label;
		$this->setUrl($url);
	}
	
	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @param string $label
	 */
	public function setLabel(string $label) {
		$this->label = $label;
	}
	
	/**
	 * @return \n2n\util\uri\Url
	 */
	public function getUrl() {
		return $this->url;
	}
	
	/**
	 * @param Url|string $url
	 */
	public function setUrl($url) {
		$this->url = Url::create($url);
	}
}