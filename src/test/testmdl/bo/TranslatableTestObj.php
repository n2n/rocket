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

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;
use n2n\l10n\N2nLocale;
use rocket\impl\ei\component\prop\translation\Translator;
use n2n\persistence\orm\attribute\OneToMany;
use n2n\persistence\orm\CascadeType;

#[EiType]
#[EiPreset(EiPresetMode::READ)]
class TranslatableTestObj {

	private int $id;
	private ?string $someLabel = null;
	#[OneToMany(TranslationTestObj::class, mappedBy: 'translatableTestObj', cascade: CascadeType::ALL, orphanRemoval: true)]
	public \ArrayObject $translatableTestObjs;

	function __construct() {
		$this->translatableTestObjs = new \ArrayObject();
	}

	function getId(): ?int {
		return $this->id ?? null;
	}

	public function getSomeLabel(): ?string {
		return $this->someLabel;
	}

	public function setSomeLabel(?string $someLabel): TranslatableTestObj {
		$this->someLabel = $someLabel;
		return $this;
	}

	public function t(N2nLocale ...$n2nLocales): TranslationTestObj {
		return Translator::requireAny($this->translatableTestObjs, ...$n2nLocales);
	}

	function addTranslationTestObj(TranslationTestObj $translationTestObj): static {
		$translationTestObj->translatableTestObj = $this;
		$this->translatableTestObjs->append($translationTestObj);
		return $this;
	}

}
