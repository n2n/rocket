<?php

namespace rocket\spec\ei\manage\mapping;

interface MappableWrapper {
	
	public function isIgnored(): bool;
	
	public function setIgnored(bool $ignored);
}

