<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiModCallback;
use rocket\attribute\impl\EiSetup;
use rocket\op\ei\util\Eiu;
use n2n\persistence\orm\attribute\OneToMany;
use rocket\impl\ei\component\prop\translation\Translatable;
use n2n\l10n\N2nLocale;
use rocket\impl\ei\component\prop\translation\TranslatableAdapter;
use n2n\persistence\orm\attribute\ManyToOne;

#[EiType]
#[EiPreset(EiPresetMode::READ)]
class TranslationTestObj extends TranslatableAdapter {

	public int $id;
	#[ManyToOne]
	public TranslatableTestObj $translatableTestObj;

	public string $name;

}
