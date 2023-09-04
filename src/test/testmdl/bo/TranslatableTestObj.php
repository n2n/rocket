<?php

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
