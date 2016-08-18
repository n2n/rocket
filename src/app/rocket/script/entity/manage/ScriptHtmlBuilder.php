<?php
namespace rocket\script\entity\manage;

use n2n\ui\html\HtmlView;
use n2n\dispatch\PropertyPath;
use rocket\script\entity\manage\display\Displayable;
use n2n\ui\html\HtmlUtils;
use rocket\script\entity\manage\model\EntryModel;
use rocket\script\entity\manage\model\EntryListModel;
use n2n\core\IllegalStateException;
use rocket\script\entity\manage\model\EditEntryModel;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\ui\html\HtmlElement;
use rocket\script\entity\manage\model\ManageModel;
use n2n\ui\Raw;

class ScriptHtmlBuilder {
	private $view;
	private $html;
	private $formHtml;
	private $displayDefinition;
	private $scriptState;
	private $meta;
	private $basePropertyPath;
	
	public function __construct(HtmlView $view, ManageModel $manageModel, PropertyPath $basePropertyPath = null) {
		$this->view = $view;
		$this->html = $view->getHtmlBuilder();
		$this->formHtml = $view->getFormHtmlBuilder();
		$this->displayDefinition = $manageModel->getDisplayDefinition();
		$this->scriptState = $manageModel->getScriptState();
		
		if ($manageModel instanceof EntryModel) {
			$this->meta = new ScriptHtmlBuilderMeta($manageModel);
		} else if ($manageModel instanceof EntryListModel) {
			$this->meta = new ScriptHtmlBuilderMeta(null, 
					$manageModel->getEntryModels());
		} else {
			$this->meta = new ScriptHtmlBuilderMeta();
		}
		
		if ($basePropertyPath !== null) {
			$this->basePropertyPath = $basePropertyPath->ext('optionForm');
		} else {
			$this->basePropertyPath = PropertyPath::createFromPropertyExpression('optionForm');
		}
	}
	
	public function meta() {
		return $this->meta;
	}
	
	public function simpleLabel($id) {
		$this->html->out($this->getSimpleLable($id));
	}
	
	public function getSimpleLable($id) {
		return $this->view->getHtmlBuilder()
				->getEsc($this->displayDefinition->getDisplayableById($id)->getDisplayLabel());
	}
	
	private function pushScriptFieldInfo($tagName, Displayable $displayable, 
			EntryModel $entryModel, PropertyPath $propertyPath = null) {
		$this->scriptFieldInfoStack[] = array('tagName' => $tagName, 'entryModel' => $entryModel, 
				'displayable' => $displayable, 'propertyPath' => $propertyPath);
	}
	
	public function peakScriptFieldInfo($pop) {
		if (!sizeof($this->scriptFieldInfoStack)) {
			throw new IllegalStateException('No ScriptField open');
		}

		if ($pop) {
			return array_pop($this->scriptFieldInfoStack);
		} else {
			return end($this->scriptFieldInfoStack);
		}
	}
	
	private function buildContainerAttrs(array $containerAttrs, array $attrs, $readOnly = true, $required = false) {
		$attrs = HtmlUtils::mergeAttrs($containerAttrs, $attrs);
		
		if ($required) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-required'), $attrs);
		}
			
		if ($readOnly) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-read-only'), $attrs);
		} else {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-editable'), $attrs);
		}
		
// 		$entityMapping = $this->entryModel->getScriptSelectionMappings();
			
// 		if ($entityMapping->containsDraftableId($displayable->getId())) {
// 			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-draftable'), $attrs);
// 		}
		
// 		if ($entityMapping->containsTranslatableId($displayable->getId())) {
// 			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-translatable'), $attrs);
// 		}
		
		return $attrs;
	}
	
	public function openInputField($tagName, $id, array $attrs = null) {
		$this->view->out($this->getOpenInputField($tagName, $id, $attrs));
	}
	
	public function getOpenInputField($tagName, $id, array $attrs = null) {
		$entryModel = $this->meta->getCurrentEntryModel();
		$displayDefinition = $entryModel->getDisplayDefinition();
		
		$editable = null;
		if ($entryModel instanceof EditEntryModel && $displayDefinition->containsEditableId($id)) {
			$editable = $displayDefinition->findProperEditable($id, $entryModel);			
		}
		
		if ($editable === null) {
			return $this->getOpenOutputField($tagName, $id, $attrs);
		}
		
		$propertyPath = $entryModel->createPropertyPath($id, $this->basePropertyPath);

		if ($this->formHtml->hasErrors($propertyPath)) {
			$attrs = HtmlUtils::mergeAttrs((array) $attrs, array('class' => 'rocket-has-error'));
		}
		
		$this->pushScriptFieldInfo($tagName, $editable, $entryModel, $propertyPath);
		return $this->formHtml->getOpenOption($tagName, $propertyPath, $this->buildContainerAttrs(
				$editable->getHtmlContainerAttrs($entryModel), 
				(array) $attrs, false, $editable->isRequired($entryModel)));
	
	}
		
	public function openOutputField($tagName, $id, array $attrs = null) {
		$this->view->out($this->getOpenOutputField($tagName, $id, $attrs));
	}
	
	public function getOpenOutputField($tagName, $id, array $attrs = null) {
		$entryModel = $this->meta->getCurrentEntryModel();
		$displayable = $entryModel->getDisplayDefinition()->getDisplayableById($id);
		
		$this->pushScriptFieldInfo($tagName, $displayable, $entryModel);
		
		return new Raw('<' . htmlspecialchars($tagName) . HtmlElement::buildAttrsHtml(
				$this->buildContainerAttrs($displayable->getHtmlContainerAttrs($entryModel), 
						(array) $attrs, true)) . '>');
	}
	
	public function closeField() {
		$this->view->out($this->getCloseField());
	}
	
	public function getCloseField() {
		$scriptFieldInfo = $this->peakScriptFieldInfo(true);
		if (isset($scriptFieldInfo['propertyPath'])) {
			return $this->formHtml->getCloseOption();
		}
		
		return new Raw('</' . htmlspecialchars($scriptFieldInfo['tagName']) . '>');
	}
	
	public function label(array $attrs = null, $label = null) {
		$this->html->out($this->getLabel($attrs, $label));
	}
	
	public function getLabel(array $attrs = null, $label = null) {
		$scriptFieldInfo = $this->peakScriptFieldInfo(false);
		$displayable = $scriptFieldInfo['displayable'];
		if (isset($scriptFieldInfo['propertyPath'])) {
			return $this->formHtml->getOptionLabel($attrs, $label);
		}
		
		return new HtmlElement('label', $attrs, ($label === null ? $displayable->getDisplayLabel() : $label));
	} 
	
	public function field() {
		$this->html->out($this->getField());
	}
	
	public function getField() {
		$scriptFieldInfo = $this->peakScriptFieldInfo(false);
		
		if (isset($scriptFieldInfo['propertyPath'])) {
			return $this->formHtml->getOptionField($scriptFieldInfo['propertyPath']);
		}
		
		$displayable = $scriptFieldInfo['displayable'];
		if ($displayable instanceof Displayable) {
			return $displayable->createUiOutputField($scriptFieldInfo['entryModel'], $this->view);
		}
		
		return null;
	}
	
	public function overallControlList() {
		$this->html->out($this->getOverallControlList());
	}
	
	public function getOverallControlList() {
		$ul = new HtmlElement('ul'/*, array('class' => 'rocket-main-controls')*/);
		foreach ($this->scriptState->getScriptMask()->createOverallControlButtons($this->scriptState, $this->view) as $control) {
			$ul->appendContent(new HtmlElement('li', null, $control->toButton(false)));
		}
	
		return $ul;
	}
	
	public function entryControlList($useIcons = false) {
		$this->html->out($this->getEntryControlList($useIcons));
	}
	
	public function getEntryControlList($useIcons = false) {
		$entryControls = $this->scriptState->getScriptMask()->createEntryControlButtons($this->scriptState, 
				$this->meta->getCurrentEntryModel()->getScriptSelectionMapping(), $this->view);
	
		$ulHtmlElement = new HtmlElement('ul', array('class' => ($useIcons ? 'rocket-simple-controls' : null /* 'rocket-main-controls' */)));
	
		foreach ($entryControls as $control) {
			$liHtmlElement = new HtmlElement('li', null, $control->toButton($useIcons));
			$ulHtmlElement->appendContent($liHtmlElement);
		}
	
		return $ulHtmlElement;
	}
}

class ScriptHtmlBuilderMeta {
	private $displayableDefinition;
	private $key;
	private $entryModel;
	private $entryModels;
	private $basePropertyPath;
	
	public function __construct(EntryModel $entryModel = null, array $entryModels = null) {
		$this->entryModel = $entryModel;
		$this->entryModels = $entryModels;
	}
	
	public function getCurrentKey() {
		return $this->key;
	}
	
	public function getCurrentEntryModel() {
		if ($this->entryModel === null) {
			throw new IllegalStateException('No EntryModel selected');
		}
		
		return $this->entryModel;
	}
	
	public function next() {
		if ($this->entryModels === null) {
			throw new IllegalStateException('No EntryModel list');
		}
		
		$entryModel = null;
		if ($this->entryModel === null) {
			$entryModel = reset($this->entryModels);
		} else {
			$entryModel = next($this->entryModels);
		}

		$key = key($this->entryModels);
		if ($entryModel === false) {
			$entryModel = null;
			$key = null;	
		}
		
		$this->entryModel = $entryModel;
		return $this->key = $key;
	}
	
	public function rewind() {
		$this->entryModel = null;
		return $this->next();
	}
}