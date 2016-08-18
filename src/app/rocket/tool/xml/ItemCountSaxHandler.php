<?php

namespace rocket\tool\xml;

class ItemCountSaxHandler implements SaxHandler {
	private $level = 0;
	private $number = 0;

	public function startElement($tagName, array $attributes) {
		$this->level++;
		if ($this->level == 2 && $tagName == 'item') {
			$this->number++;
		}
	}

	public function cdata($cdata) { }

	public function endElement($tag) {
		$this->level--;
	}
	
	public function getNumber() {
		return $this->number;
	}
}
