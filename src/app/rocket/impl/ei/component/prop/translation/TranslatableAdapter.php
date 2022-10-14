<?php

namespace rocket\impl\ei\component\prop\translation;

use n2n\l10n\N2nLocale;
use n2n\persistence\orm\attribute\MappedSuperclass;

#[MappedSuperclass]
class TranslatableAdapter implements Translatable {

	private N2nLocale $n2nLocale;

	public function getN2nLocale() {
		return $this->n2nLocale;
	}

	public function setN2nLocale(N2nLocale $n2nLocale) {
		$this->n2nLocale = $n2nLocale;
	}

}