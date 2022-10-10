<?php

namespace testmdl\bo;

use n2n\context\attribute\ThreadScoped;
use rocket\ei\util\Eiu;
use rocket\attribute\impl\EiSetup;

#[ThreadScoped]
class ModTestMod {

	#[EiSetup]
	private function setup(Eiu $eiu) {

	}

}