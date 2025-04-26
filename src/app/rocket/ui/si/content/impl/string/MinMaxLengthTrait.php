<?php

namespace rocket\ui\si\content\impl\string;

trait MinMaxLengthTrait {

	private ?int $minlength;
	private ?int $maxlength;

	function setMinlength(?int $minlength): static {
		$this->minlength = $minlength;
		return $this;
	}

	function getMinlength(): ?int {
		return $this->minlength;
	}

	function setMaxlength(?int $maxlength): static {
		$this->maxlength = $maxlength;
		return $this;
	}

	function getMaxlength(): ?int {
		return $this->maxlength;
	}
}
