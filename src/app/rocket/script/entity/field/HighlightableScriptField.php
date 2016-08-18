<?php
namespace rocket\script\entity\field;

use n2n\persistence\orm\Entity;
use n2n\l10n\Locale;

interface HighlightableScriptField extends ScriptField {
	public function createKnownString(Entity $entity, Locale $locale);
}