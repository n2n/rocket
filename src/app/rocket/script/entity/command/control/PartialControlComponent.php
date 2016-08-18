<?php
namespace rocket\script\entity\command\control;

use n2n\ui\html\HtmlView;
use rocket\script\entity\manage\ScriptState;
use n2n\l10n\Locale;

interface PartialControlComponent {
	public function getPartialControlOptions(Locale $locale);
	
	public function createPartialControlButtons(ScriptState $scriptState, HtmlView $htmlView);
	
	public function processEntries(ScriptState $scriptState, array $entries);
}