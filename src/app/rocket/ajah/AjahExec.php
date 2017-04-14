<?php
namespace rocket\ajah;

class AjahExec {
	private $forceReload = false;
	private $showLoadingContext = true;

	public function __construct(bool $forceReload = false, bool $showLoadingContext = true) {
		$this->forceReload = $forceReload;
		$this->showLoadingContext = $showLoadingContext;
	}

	public function toAttrs() {
		return array(
				'forceReload' => $this->forceReload,
				'showLoadingcontext' => $this->showLoadingContext);
	}
}
