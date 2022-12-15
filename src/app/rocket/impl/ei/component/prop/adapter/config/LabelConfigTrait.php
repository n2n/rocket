<?php

namespace rocket\impl\ei\component\prop\adapter\config;

use n2n\l10n\Lstr;
use n2n\util\StringUtils;

trait LabelConfigTrait {

	protected ?LabelConfig $labelConfig;

	function getLabelConfig(): LabelConfig {
		return $this->labelConfig ?? $this->labelConfig = new LabelConfig();
	}

	function setLabel(?string $label): static {
		$this->getLabelConfig()->setLabel($label);
		return $this;
	}

	function getLabel(): ?string {
		return $this->getLabelConfig()->getLabel();
	}

	public function getLabelLstr(): Lstr {
		$label = $this->getLabelConfig()->getLabel()
				?? StringUtils::pretty((new \ReflectionClass($this))->getShortName());

		return Lstr::create($label);
	}

	function getHelpText(): ?string {
		return $this->getLabelConfig()->getHelpText();
	}

	function setHelpText(?string $helpText): static {
		$this->getLabelConfig()->setHelpText($helpText);
		return $this;
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