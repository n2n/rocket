<?php
namespace rocket\script\entity\command;

use n2n\l10n\Locale;

interface PrivilegeExtendableScriptCommand {
	/**
	 * @param Locale $locale
	 * @return array the key is privilege name and the value its label
	 */
	public function getPrivilegeExtOptions(Locale $locale);
}