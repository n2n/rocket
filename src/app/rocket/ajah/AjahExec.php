<?php
namespace rocket\ajah;

class JhtmlExec {
	private $forceReload = false;
	private $pushToHistory = true;

	public function __construct(bool $forceReload = false, bool $pushToHistory = true) {
		$this->forceReload = $forceReload;
		$this->pushToHistory = $pushToHistory;
	}

	public function toAttrs() {
		return array(
				'forceReload' => $this->forceReload,
				'pushToHistory' => $this->pushToHistory);
	}
}
