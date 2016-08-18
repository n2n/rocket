<?php
namespace rocket\script\entity\command\control;

use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\manage\ScriptState;
use n2n\ui\html\HtmlView;
use n2n\l10n\Locale;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;

interface EntryControlComponent {
	/**
	 * @param Locale $locale
	 * @return array
	 */
	public function getEntryControlOptions(Locale $locale);
	/**
	 * @param ScriptSelectionMapping $scriptSelectionMapping
	 * @param HtmlView $htmlView
	 * @return \rocket\script\entity\command\control\ControlButton[]
	 */
	public function createEntryControlButtons(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping, HtmlView $htmlView);
}