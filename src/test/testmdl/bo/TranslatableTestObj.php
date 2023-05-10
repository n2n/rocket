<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiModCallback;
use rocket\attribute\impl\EiSetup;
use rocket\op\ei\util\Eiu;
use n2n\persistence\orm\attribute\ManyToOne;
use n2n\impl\persistence\orm\property\relation\Relation;
use n2n\l10n\N2nLocale;
use page\bo\PageT;
use rocket\impl\ei\component\prop\translation\Translator;
use n2n\persistence\orm\attribute\ManyToMany;
use n2n\persistence\orm\attribute\OneToMany;
use n2n\persistence\orm\CascadeType;

#[EiType]
#[EiPreset(EiPresetMode::READ)]
class TranslatableTestObj {

	private int $id;
	#[OneToMany(TranslationTestObj::class, mappedBy: 'translatableTestObj', cascade: CascadeType::ALL, orphanRemoval: true)]
	public \ArrayObject $translatableTestObjs;


	/**
	 *
	 * @param N2nLocale ...$n2nLocales
	 * @return PageT
	 */
	public function t(N2nLocale ...$n2nLocales): TranslationTestObj {
		return Translator::requireAny($this->translatableTestObjs, ...$n2nLocales);
	}

}
