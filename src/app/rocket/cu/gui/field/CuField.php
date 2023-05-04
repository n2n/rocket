<?php

namespace rocket\cu\gui\field;

use rocket\si\content\SiField;

interface CuField {

	function getSiField(): SiField;

	function validate(): void;
}