<?php

namespace rocket\script\entity\mask;

use rocket\script\entity\manage\ScriptState;
use n2n\ui\html\HtmlView;
use rocket\script\entity\manage\ScriptSelection;
use n2n\persistence\orm\Entity;
use n2n\l10n\Locale;
use rocket\script\entity\manage\model\EntryListModel;
use rocket\script\entity\manage\model\EntryModel;
use rocket\script\entity\manage\model\EditEntryModel;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;

interface ScriptMask {
	public function getLabel();
	public function getPluralLabel();
	public function isDraftEnabled();
	public function isTranslationEnabled();
	/**
	 * @return \rocket\script\entity\command\ScriptCommand[] key must be the id of the command 
	 */
	public function getCommands();
	public function hasOverviewCommand();
	/**
	 * @return \rocket\script\entity\command\OverviewScriptCommand
	 */
	public function getOverviewCommand();
	public function hasEntryDetailCommand();
	/**
	 * @return \rocket\script\entity\command\EntryDetailScriptCommand
	 */
	public function getEntryDetailCommand();
	public function hasEntryAddCommand();
	/**
	 * @return \rocket\script\entity\command\EntryAddScriptCommand 
	 */
	public function getEntryAddCommand();
	/**
	 * @param ScriptState $scriptState
	 * @param HtmlView $htmlView
	 * @return \rocket\script\entity\command\ControlButton[]
	 */
	public function createOverallControlButtons(ScriptState $scriptState, HtmlView $htmlView);
	/**
	 * @param ScriptState $scriptState
	 * @param ScriptSelection $scriptSelection
	 * @param HtmlView $htmlView
	 * @return \rocket\script\entity\command\ControlButton[]
	 */
	public function createEntryControlButtons(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping, HtmlView $htmlView);
	/**
	 * @param Entity $entity
	 * @param Locale $locale
	 * @return string
	 */
	public function createKnownString(Entity $entity, Locale $locale);
	
	public function createDisplayDefinition(ScriptState $scriptState, $draftableEditablesOnly = false, 
			$translatableEditablesOnly = false, $levelOnly = false);
	
	public function createListView(EntryListModel $entryListModel);
	
	public function createEditEntryView(EditEntryModel $editEntryModel);
	
	public function createEntryView(EntryModel $entryModel);
	
	public function getFilterData();
	
	public function getDefaultSortDirections();
}