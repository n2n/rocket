<?php

namespace rocket\ei\component\prop;

use rocket\ei\manage\generic\ScalarEiProperty;

interface ScalarEiProp extends FieldEiProp {

	public function getScalarEiProperty(): ?ScalarEiProperty;
}