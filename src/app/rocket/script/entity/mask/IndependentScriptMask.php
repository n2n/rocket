<?php

namespace rocket\script\entity\mask;

use rocket\script\entity\manage\ScriptState;
use n2n\ui\html\HtmlView;
use rocket\script\entity\command\control\EntryControlComponent;
use rocket\script\entity\command\control\OverallControlComponent;
use rocket\script\entity\EntityScript;
use rocket\script\core\extr\ScriptMaskExtraction;
use rocket\script\entity\field\DisplayableScriptField;
use rocket\script\entity\command\IndependentScriptCommand;
use rocket\util\Identifiable;
use rocket\script\entity\command\control\PartialControlComponent;
use n2n\l10n\Locale;
use rocket\script\entity\command\OverviewScriptCommand;
use rocket\script\entity\UnknownScriptElementException;
use rocket\script\entity\command\EntryDetailScriptCommand;
use rocket\script\entity\command\EntryAddScriptCommand;
use n2n\ui\ViewFactory;
use n2n\N2N;
use rocket\script\entity\manage\model\EntryListModel;
use rocket\script\entity\manage\model\EntryModel;
use rocket\script\entity\manage\model\EditEntryModel;
use rocket\script\entity\manage\display\DisplayDefinition;
use rocket\script\entity\field\EditableScriptField;
use n2n\persistence\orm\Entity;
use n2n\dispatch\PropertyPath;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\mask\GroupedFieldOrder;
use rocket\script\entity\manage\model\EntryTreeListModel;
use rocket\script\entity\field\DraftableScriptField;
use rocket\script\entity\field\TranslatableScriptField;
use rocket\script\entity\manage\model\FieldOrderViewModel;
use n2n\util\Attributes;

class IndependentScriptMask implements ScriptMask, Identifiable {
	private $entityScript;
	private $extraction;
	
	public function __construct(EntityScript $entityScript, ScriptMaskExtraction $extraction) {
		$this->entityScript = $entityScript;
		$this->extraction = $extraction;
	}
	/**
	 * @return ScriptMaskExtraction
	 */
	public function getExtraction() {
		return $this->extraction;
	}
	
	public function getId() {
		return $this->extraction->getId();
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\mask\ScriptMask::getEntityScript()
	 */
	public function getEntityScript() {
		return $this->entityScript;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\mask\ScriptMask::getLabel()
	 */
	public function getLabel() {
		if (null !== ($label = $this->extraction->getLabel())) {
			return $label;
		}
		
		return $this->entityScript->getLabel();
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\mask\ScriptMask::getPluralLabel()
	 */
	public function getPluralLabel() {
		if (null !== ($pluralLabel = $this->extraction->getPluralLabel())) {
			return $pluralLabel;
		}
		
		return $this->entityScript->getPluralLabel();
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\mask\ScriptMask::isDraftDisabled()
	 */
	public function isDraftEnabled() {
		return !$this->extraction->isDraftDisabled() && $this->entityScript->isDraftEnabled();
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\mask\ScriptMask::isTranslationDisabled()
	 */
	public function isTranslationEnabled() {
		return !$this->extraction->isTranslationDisabled() && $this->entityScript->isTranslationEnabled();
	}
	
	public function createDisplayDefinition(ScriptState $scriptState, $draftableEditablesOnly = false, $translatableEditablesOnly = false, $levelOnly = false) {
		$displayDefinition = new DisplayDefinition($this->entityScript, $this);
		foreach ($this->entityScript->getFieldCollection()->filter($levelOnly) as $field) {
			if ($field instanceof EditableScriptField
					&& (!$draftableEditablesOnly || ($field instanceof DraftableScriptField && $field->isDraftEnabled()))
					&& (!$translatableEditablesOnly || ($field instanceof TranslatableScriptField && $field->isTranslationEnabled()))) {
				
				$displayDefinition->registerEditable($field->getId(), $field->createEditable($scriptState, new Attributes()));
				continue;
			}
			
			if ($field instanceof DisplayableScriptField) {
				$displayDefinition->registerDisplayable($field->getId(), $field->createDisplayable($scriptState, new Attributes()));
			}
		}
				
		foreach ($this->entityScript->getModificatorCollection() as $constraint) {
			$constraint->setupDisplayDefinition($displayDefinition);
		}
		
		return $displayDefinition;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\mask\ScriptMask::getCommands()
	 */
	public function getCommands() {
		if (null !== ($commandIds = $this->extraction->getCommandIds())) {
			$commands = array();
			foreach ($this->entityScript->getCommandCollection() as $id => $command) {
				if ($command instanceof IndependentScriptCommand && !in_array($id, $commandIds)) {
					continue;
				}
				
				$commands[$id] = $command;
			}
			return $commands;
		}
		
		return $this->entityScript->getCommandCollection()->toArray();
	}
	
	private function findOverviewCommand() {
		$overviewCommandId = $this->extraction->getOverviewCommandId();
		foreach ($this->getCommands() as $command) {
			if ($command instanceof OverviewScriptCommand 
					&& ($overviewCommandId === null || $command->getId() == $overviewCommandId)) {
				return $command;
			}
		}
		
		return null;
	}
	
	public function hasOverviewCommand() {
		return null !== $this->findOverviewCommand();
	}
	
	public function getOverviewCommand() {
		$overviewCommand = $this->findOverviewCommand();
		if ($overviewCommand === null) {
			throw $this->createUnknownScriptCommandException('rocket\script\entity\command\OverviewScriptCommand', 
					$this->extraction->getOverviewCommandId());
		}
		return  $overviewCommand;
	}
	
	private function findEntryDetailCommand() {
		$entryDetailCommandId = $this->extraction->getEntryDetailCommandId();
		foreach ($this->getCommands() as $command) {
			if ($command instanceof EntryDetailScriptCommand 
					&& ($entryDetailCommandId === null || $command->getId() == $entryDetailCommandId)) {
				return $command;
			}
		}
		
		return null;
	}
	
	public function hasEntryDetailCommand() {
		return null !== $this->findEntryDetailCommand();
	}
	
	public function getEntryDetailCommand() {
		$entryDetailCommand = $this->findEntryDetailCommand();
		if ($entryDetailCommand === null) {
			throw $this->createUnknownScriptCommandException('rocket\script\entity\command\EntryDetailScriptCommand', 
					$this->extraction->getEntryDetailCommandId());
		}
		return $entryDetailCommand;
	}
	
	private function findEntryAddCommand() {
		$entryAddCommandId = $this->extraction->getEntryAddCommandId();
		foreach ($this->getCommands() as $command) {
			if ($command instanceof EntryAddScriptCommand 
					&& ($entryAddCommandId === null || $command->getId() == $entryAddCommandId)) {
				return $command;
			}
		}
		
		return null;
	}
	
	public function hasEntryAddCommand() {
		return null !== $this->findEntryAddCommand();
	}
	
	public function getEntryAddCommand() {
		$entryAddCommand = $this->findEntryAddCommand();
		if ($entryAddCommand === null) {
			throw $this->createUnknownScriptCommandException('rocket\script\entity\command\EntryAddScriptCommand', 
					$this->extraction->getEntryDetailCommandId());
		}
		return $entryAddCommand;
	}
	
	private function createUnknownScriptCommandException($type, $commandId) {
		throw new UnknownScriptElementException('No ' . $type
				. ($commandId !== null ? ' with Id ' . $commandId : '') .  ' available '
				. (null !== ($maskId = $this->getId()) ? ' in Mask ' . $maskId . ' of': 'in')
				. ' EntityScript ' . $this->getEntityScript()->getId());
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\mask\ScriptMask::createKnownString()
	 */
	public function createKnownString(Entity $entity, Locale $locale) {
		return $this->entityScript->createKnownString($entity, $locale);
	}

	const BUTTON_ID_PARTIAL_SEPARATOR = '?PARTIAL?';
	const BUTTON_ID_OVERALL_SEPARATOR = '?OVERALL?';
	const BUTTON_ID_ENTRY_SEPARATOR = '?ENTRY?';
	/**
	 * @param unknown $commandId
	 * @param unknown $separator
	 * @param unknown $buttonId
	 * @return string
	 */
	private function buildButtonId($commandId, $separator, $buttonId) {
		return $commandId . $separator . $buttonId;
	}
	/**
	 * @param array $controls
	 * @param array $order
	 * @return array
	 */
	private function sortControls(array $controls, array $order) {
		$sortedControls = array();
		foreach ($order as $key) {
			if (!isset($controls[$key])) continue;
			$sortedControls[$key] = $controls[$key];
			unset($controls[$key]);
		}
	
		return array_merge($sortedControls, $controls);
	}
	/**
	 * @param Locale $locale
	 * @return array
	 */
	public function getPartialControlOptions(Locale $locale) {
		$labels = array();
		
		foreach ($this->entityScript->getCommandCollection() as $id => $scriptCommand) {
			if (!($scriptCommand instanceof PartialControlComponent)) continue;
			
			foreach ($scriptCommand->getPartialControlOptions($locale) as $key => $label) {
				$labels[$this->buildButtonId($id, self::BUTTON_ID_PARTIAL_SEPARATOR, $key)] = $label;
			}
		}
		
		return $this->sortControls($labels, $this->extraction->getPartialControlOrder());
	}
	/**
	 * @param Locale $locale
	 * @return array
	 */
	public function getOverallControlOptions(Locale $locale) {
		$labels = array();
		
		foreach ($this->entityScript->getCommandCollection() as $id => $scriptCommand) {
			if (!($scriptCommand instanceof OverallControlComponent)) continue;
			
			foreach ($scriptCommand->getOverallControlOptions($locale) as $key => $label) {
				$labels[$this->buildButtonId($id, self::BUTTON_ID_OVERALL_SEPARATOR, $key)] = $label;
			}
		}
		
		return $this->sortControls($labels, $this->extraction->getOverallControlOrder());
	}
	/**
	 * @param Locale $locale
	 * @return array
	 */
	public function getEntryControlOptions(Locale $locale) {
		$labels = array();
		
		foreach ($this->entityScript->getCommandCollection() as $id => $scriptCommand) {
			if (!($scriptCommand instanceof EntryControlComponent)) continue;
			
			foreach ($scriptCommand->getEntryControlOptions($locale) as $key => $label) {
				$labels[$this->buildButtonId($id, self::BUTTON_ID_ENTRY_SEPARATOR, $key)] = $label;
			}
		}
		
		return $this->sortControls($labels, $this->extraction->getEntryControlOrder());
	}
	/**
	 * @param ScriptState $scriptState
	 * @param HtmlView $htmlView
	 * @return \rocket\script\entity\command\ControlButton[]
	 */
	public function createOverallControlButtons(ScriptState $scriptState, HtmlView $htmlView) {
		$controls = array();
		foreach ($this->getCommands() as $id => $scriptCommand) {
			if (!($scriptCommand instanceof OverallControlComponent)
					|| !$scriptState->isScriptCommandAvailable($scriptCommand)) continue;
				
			foreach ((array) $scriptCommand->createOverallControlButtons($scriptState, $htmlView) as $key => $control) {
				$controls[$this->buildButtonId($id, self::BUTTON_ID_OVERALL_SEPARATOR, $key)] = $control;
			}
		}
	
		return $this->sortControls($controls, $this->extraction->getOverallControlOrder());
	}
	/**
	 * @param ScriptSelectionMapping $scriptSelectionMapping
	 * @param HtmlView $view
	 * @return \rocket\script\entity\command\ControlButton[]
	 */
	public function createEntryControlButtons(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view) {
		$controls = array();
		foreach ($this->getCommands() as $id => $scriptCommand) {
			if (!($scriptCommand instanceof EntryControlComponent)
					|| !$scriptSelectionMapping->isAccessableBy($scriptCommand)) {
				continue;
			}
				
			foreach ((array) $scriptCommand->createEntryControlButtons($scriptState, $scriptSelectionMapping, $view) as $key => $control) {
				$controls[$this->buildButtonId($id, self::BUTTON_ID_ENTRY_SEPARATOR, $key)] = $control;
			}
		}
	
		return $this->sortControls($controls, $this->extraction->getEntryControlOrder());
	}
	
	public function createListView(EntryListModel $entryListModel) {
		$fieldOrder = $this->extraction->getListFieldOrder();
		if ($fieldOrder === null) {
			$fieldOrder = array();
			foreach ($this->entityScript->getFieldCollection() as $field) {
				if ($field instanceof DisplayableScriptField && $field->isDisplayInListViewEnabled()) {
					$fieldOrder[] = $field->getId();
				}
			}
		}
		
		if ($entryListModel instanceof EntryTreeListModel) {
			return ViewFactory::create('rocket\script\entity\manage\view\entryTreeList.html',
					N2N::getModule('rocket'), array('fieldIds' => $fieldOrder,
							'entryTreeListModel' => $entryListModel));
		}
		
		return ViewFactory::create('rocket\script\entity\manage\view\entryList.html', 
				N2N::getModule('rocket'), array('fieldIds' => $fieldOrder, 
						'entryListModel' => $entryListModel));
	}
	
	
	public function createEntryView(EntryModel $entryModel) {		
		$fieldOrder = $this->extraction->getDetailFieldOrder();
		
		if ($fieldOrder === null) {
			$fieldOrder = $this->extraction->getEntryFieldOrder();
		}
		
		if ($fieldOrder === null) {
			$fieldOrder = array();
			foreach ($this->entityScript->getFieldCollection() as $field) {
				if ($field instanceof DisplayableScriptField && $field->isDisplayInDetailViewEnabled()) {
					$fieldOrder[] = $field->getId();
				}
			}
		}
		
		return ViewFactory::create('rocket\script\entity\manage\view\entry.html',
				N2N::getModule('rocket'), array('fieldOrderViewModel' => new FieldOrderViewModel($fieldOrder), 
						'entryModel' => $entryModel));
	}
	
	public function createEditEntryView(EditEntryModel $editEntryModel, PropertyPath $basePropertyPath = null) {
		$new = $editEntryModel->getScriptSelectionMapping()->getScriptSelection()->isNew();
		$fieldOrder = null;
		if ($new) {
			$fieldOrder = $this->extraction->getAddFieldOrder();
		} else {
			$fieldOrder = $this->extraction->getEditFieldOrder();
		}
		
		if ($fieldOrder === null) {
			$fieldOrder = $this->extraction->getEntryFieldOrder();
		}
		
		if ($fieldOrder === null) {
			$fieldOrder = array();
			foreach ($this->entityScript->getFieldCollection() as $field) {
				if ($field instanceof DisplayableScriptField 
						&& ((!$new && $field->isDisplayInEditViewEnabled())
								|| ($new && $field->isDisplayInAddViewEnabled()))) {
					$fieldOrder[] = $field->getId();
				}
			}
		}
		
		$fieldOrder = $this->filterFieldOrder($fieldOrder, $editEntryModel->getDisplayDefinition());
		
		return ViewFactory::create('rocket\script\entity\manage\view\entryEdit.html',
				N2N::getModule('rocket'), array('fieldOrderViewModel' => new FieldOrderViewModel($fieldOrder), 
						'editEntryModel' => $editEntryModel, 'basePropertyPath' => $basePropertyPath));
	}
	
	private function filterFieldOrder(array $fieldOrder, DisplayDefinition $displayDefinition) {
		foreach ($fieldOrder as $key => $fieldId) {
			if ($fieldId instanceof GroupedFieldOrder) {
				$group = $fieldId->copy($this->filterFieldOrder(
						$fieldId->getFieldOrder(), $displayDefinition));
				if ($group->size()) {
					$fieldOrder[$key] = $group;
					continue;
				}
			}
			
			if (!$displayDefinition->containsDisplayableId($fieldId)) {
				unset($fieldOrder[$key]);
			}
		}
		return $fieldOrder;
	}
	
	public function getFilterData() {
		return $this->extraction->getFilterData();
	}
	
	public function getDefaultSortDirections() {
		if (null !== ($sortDirections = $this->extraction->getDefaultSortDirections())) {
			return $sortDirections;
		}
		
		return $this->entityScript->getDefaultSortDirections();
	}
	
	public function isFiltered()  {
		return null !== $this->extraction->getFilterData();
	}
	
	public function determineScriptMask($entityScriptId) {
		if ($this->entityScript->getId() == $entityScriptId) {
			return $this;
		}

		if ($this->entityScript->containsSubEntityScriptId($entityScriptId)) {
			return $this->getSubMaskByEntityScriptId($entityScriptId);
		}
				
		foreach ($this->entityScript->getSubEntityScripts() as $subEntityScript) {
			if (!$subEntityScript->containsSubEntityScriptId($entityScriptId, true)) continue;
			return $this->getSubMaskByEntityScriptId($subEntityScript->getId())
					->determineScriptMask($entityScriptId);
		}
		
		throw new \InvalidArgumentException();
	}
	
	public function getSubMaskByEntityScriptId($entityScriptId) {
		$subMaskIds = $this->extraction->getSubMaskIds();
		
		foreach ($this->entityScript->getSubEntityScripts() as $subEntityScript) {
			if ($subEntityScript->getId() != $entityScriptId) continue;
			
			if (isset($subMaskIds[$entityScriptId])) {
				return $subEntityScript->getMaskById($subMaskIds[$entityScriptId]);
			} else {
				return $subEntityScript->getOrCreateDefaultMask();
			}
		}
		
		throw new \InvalidArgumentException('EntityScript ' . $entityScriptId . ' is no SubEntityScript of ' 
				. $this->entityScript->getId());
	}
}