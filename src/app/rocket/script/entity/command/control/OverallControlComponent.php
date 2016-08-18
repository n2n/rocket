<?php
namespace rocket\script\entity\command\control;

use n2n\ui\html\HtmlView;
use rocket\script\entity\manage\ScriptState;
use n2n\l10n\Locale;

interface OverallControlComponent {
	public function getOverallControlOptions(Locale $locale);
	
	public function createOverallControlButtons(ScriptState $scriptState, HtmlView $htmlView);
}