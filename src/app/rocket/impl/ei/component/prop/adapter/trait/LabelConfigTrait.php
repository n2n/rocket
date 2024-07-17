<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\impl\ei\component\prop\adapter\trait;

use n2n\l10n\Lstr;
use n2n\util\StringUtils;

trait LabelConfigTrait {

	protected ?\rocket\impl\ei\component\prop\adapter\config\LabelConfig $labelConfig;

	function getLabelConfig(): \rocket\impl\ei\component\prop\adapter\config\LabelConfig {
		return $this->labelConfig ?? $this->labelConfig = new \rocket\impl\ei\component\prop\adapter\config\LabelConfig();
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
	 * @see \rocket\op\ei\component\prop\EiPropNature::getHelpTextLstr()
	 */
	public function getHelpTextLstr(): ?Lstr {
		$helpText = $this->getLabelConfig()->getHelpText();
		if ($helpText === null) {
			return null;
		}

		return Lstr::create($helpText);
	}
}