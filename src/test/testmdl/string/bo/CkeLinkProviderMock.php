<?php

namespace testmdl\string\bo;

use rocket\impl\ei\component\prop\string\cke\model\CkeLinkProvider;
use n2n\l10n\N2nLocale;
use n2n\web\ui\view\View;
use n2n\util\ex\NotYetImplementedException;

class CkeLinkProviderMock implements CkeLinkProvider {

	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		throw new NotYetImplementedException();
	}

	/**
	 * @inheritDoc
	 */
	public function getLinkOptions(N2nLocale $n2nLocale): array {
		throw new NotYetImplementedException();
	}

	/**
	 * @inheritDoc
	 */
	public function buildUrl(string $key, View $view, N2nLocale $n2nLocale) {
		throw new NotYetImplementedException();
	}

	/**
	 * @inheritDoc
	 */
	public function isOpenInNewWindow(): bool {
		throw new NotYetImplementedException();
	}
}