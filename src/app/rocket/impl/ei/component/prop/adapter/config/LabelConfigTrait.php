<?php

namespace rocket\impl\ei\component\prop\adapter\config;

use n2n\l10n\Lstr;
use n2n\util\StringUtils;

trait LabelConfigTrait {

	protected ?LabelConfig $labelConfig;

	function getLabelConfig(): LabelConfig {
		return $this->labelConfig ?? $this->labelConfig = new LabelConfig();
	}

	public function getLabelLstr(): Lstr {
		$label = $this->getLabelConfig()->getLabel()
				?? StringUtils::pretty((new \ReflectionClass($this))->getShortName());

		return Lstr::create($label);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\EiPropNature::getHelpTextLstr()
	 */
	public function getHelpTextLstr(): ?Lstr {
		$helpText = $this->getLabelConfig()->getHelpText();
		if ($helpText === null) {
			return null;
		}

		return Lstr::create($helpText);
	}
}