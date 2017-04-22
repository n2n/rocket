<?php

namespace rocket\spec\ei\manage\mapping;

interface EiFieldWrapper {
	
	public function isIgnored(): bool;
	
	public function setIgnored(bool $ignored);
}

