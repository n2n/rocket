<?php

namespace rocket\spec\ei\component\field\impl\string\cke\model;

use n2n\l10n\N2nLocale;

abstract class CkeLinkProviderAdapter implements CkeLinkProvider {
	
	public function buildUrl(string $key, N2nLocale $n2nLocale) {
		return null;
	}
	
	/**
	 * @return bool
	 */
	public function isOpenInNewWindow(): bool {
		return false;
	}
}