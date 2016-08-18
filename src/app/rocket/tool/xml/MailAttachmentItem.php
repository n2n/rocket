<?php
namespace rocket\tool\xml;

class MailAttachmentItem {
	private $path = '';
	private $name = '';
	
	public function getPath() {
		return $this->path;
	}

	public function setPath($path) {
		$this->path .= $path;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name .= $name;
	}
}
