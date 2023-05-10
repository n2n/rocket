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
use rocket\impl\ei\component\prop\ci\model\ContentItem;
use n2n\persistence\orm\CascadeType;

#[EiType]
#[EiPreset(EiPresetMode::READ)]
class CiContainerTestObj extends TranslatableAdapter {

	public int $id;
	#[OneToMany(ContentItem::class, cascade: CascadeType::ALL, orphanRemoval: true)]
	public \ArrayObject $contentItems;

}
