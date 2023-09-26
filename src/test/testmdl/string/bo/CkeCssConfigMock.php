<?php

namespace testmdl\string\bo;

use rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig;
use n2n\util\ex\NotYetImplementedException;

class CkeCssConfigMock implements CkeCssConfig {

	/**
	 * @inheritDoc
	 */
	public function getBodyId(): ?string {
		throw new NotYetImplementedException();
	}

	/**
	 * @inheritDoc
	 */
	public function getBodyClass(): ?string {
		throw new NotYetImplementedException();
	}

	/**
	 * @inheritDoc
	 */
	public function getAdditionalStyles(): ?array {
		throw new NotYetImplementedException();
	}

	/**
	 * @inheritDoc
	 */
	public function getFormatTags(): ?array {
		throw new NotYetImplementedException();
	}
}