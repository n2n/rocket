<?php
namespace rocket\script\entity\command;

use n2n\l10n\Locale;

interface PrivilegedScriptCommand {
	public function getPrivilegeLabel(Locale $locale);
}