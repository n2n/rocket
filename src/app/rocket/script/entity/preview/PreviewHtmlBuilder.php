<?php
namespace rocket\script\entity\preview;

use n2n\ui\Raw;
use n2n\ui\UiUtils;
use n2n\ui\html\Form;
use rocket\script\entity\manage\model\EntryModel;
use n2n\persistence\orm\OrmUtils;
use n2n\ui\html\HtmlView;
use n2n\l10n\Locale;
use n2n\persistence\orm\Entity;
use rocket\script\entity\UnknownScriptElementException;

class PreviewHtmlBuilder {
	private $view;
	private $previewModel;
	private $entryModel;
	private $scriptState;
	private $scriptSelection;
	private $locale;
	private $entityScript;
	private $areaObject;
	private $areaEditable;
	
	public function __construct(HtmlView $view, PreviewModel $previewModel = null, Locale $locale = null) {
		$this->view = $view;
		$this->previewModel = $previewModel;
		$this->locale = $locale;
		
		if (isset($previewModel)) {
			$this->scriptState = $this->previewModel->getScriptState();
			$this->entryModel = $previewModel->getEntryModel();
			$this->entityScript = $this->entryModel->getEntityScript();
		}
		
		// $this->view->getHtmlBuilder()->addJs('js/preview-inpage.js', 'rocket');
		// $this->view->getHtmlBuilder()->addCss('css/preview-inpage.css', null, 'rocket');
	}
	
	public function openArea(Entity $areaObject = null) {
		if (isset($this->areaObject)) {
			$this->html->getView()->throwRuntimeException(
					new PreviewAreaException('Preview area already opened.'));
		}
		
		if (isset($areaObject)) {
			if (is_null($this->previewModel)) {
				$this->areaEditable = false;
				$this->areaObject = $areaObject;
				return;
			}
						
			$scriptSelection = $this->entryModel->getScriptSelection();
			$areaObjectId = OrmUtils::extractId($areaObject, $this->entityScript->getEntityModel());
			if ($scriptSelection->getId() != $areaObjectId) {
				$this->areaEditable = false;
				$this->areaObject = $areaObject;
				return;
			}
		} else if (is_null($this->entryModel)) {
			$this->view->throwRuntimeException(
					new PreviewAreaException('No object given'));
		}
		
		$this->areaObject = $this->entryModel->getScriptSelection()->getEntity();
		$this->areaEditable = $this->entryModel instanceof EditEntryModel;

		if ($this->areaEditable && $this->previewModel->hasMainDispatchable()) {
			$this->view->getFormHtmlBuilder()->open($this->previewModel->getMainDispatchable(), Form::ENCTYPE_MULTIPART, 
					null, array('class' => 'rocket-preview-component', 'id' => 'rocket-preview-inpage-form',
							'data-rocket-preview-message-list' => $this->view->getHtmlBuilder()->getMessageList()->getContents()));
		}	
	}
	
	public function field($propertyName, \Closure $customPreviewCallback) {
		$this->view->out($this->getField($propertyName, $customPreviewCallback));
	}
	
	private function createUiElementCallback(\Closure $customPreviewCallback = null) {
		$response = $this->view->getResponse();
		$areaObject = $this->areaObject;
		return function() use ($areaObject, $customPreviewCallback, $response) {
			$ob = $response->createOutputBuffer();
			$ob->start();
			$customPreviewCallback($areaObject);
			$ob->end();
			return new Raw($ob->getBufferedContents());
		};
	}
	
	public function getField($propertyName, \Closure $customPreviewCallback) {
		$this->ensurePreviewAreaIsOpen();
		
		$createUiElementCallback = $this->createUiElementCallback($customPreviewCallback);
		
		if (!$this->areaEditable) {
// 			if (!isset($customPreviewCallback)) {
// 				$this->view->throwRuntimeException(
// 						UiUtils::createCouldNotRenderUiComponentException(
// 								new \InvalidArgumentException('No customPreviewCallback defined.')));
// 			}
			
			return $createUiElementCallback();
		}
		
		$scriptField = null;
		try {
			$scriptField = $this->entityScript->getScriptFieldByPropertyName($propertyName);
		} catch (UnknownScriptElementException $e) {
			$this->view->throwRuntimeException(
					UiUtils::createCouldNotRenderUiComponentException($e));
		}
		
		if (!($scriptField instanceof PreviewableScriptField)) {
			$this->view->throwRuntimeException(
					new PreviewAreaException('ScriptField \'' . get_class($scriptField) . '\' is not previewable.'));
		}
		
		if ($this->entryModel->containsPropertyName($propertyName)) {
			return $scriptField->createEditablePreviewUiComponent($this->previewModel, 
					$this->previewModel->createPropertyPath($propertyName), $this->view, $createUiElementCallback);
		} else {
			return $createUiElementCallback();
		}
	}
	
	private function ensurePreviewAreaIsOpen() {
		if (isset($this->areaObject)) return;
		$this->view->throwRuntimeException(
				new PreviewAreaException('Preview area was not opened.'));
	}
	
	public function closeArea() {
		$this->ensurePreviewAreaIsOpen();	
		
		if ($this->areaEditable && $this->previewModel->hasMainDispatchable()) {
			$scriptSelection = $this->entryModel->getScriptSelection();
			
			$formHtml = $this->view->getFormHtmlBuilder();
			$this->view->out('<div class="rocket-preview-inpage-component rocket-preview-inpage-commands">');	
			$this->view->out('<input type="submit" class="rocket-preview-inpage-component" />');
			$formHtml->hiddenCommand('save', array('id' => 'rocket-preview-save-command', 'disabled' => 'disabled'));
			
			$this->view->out('</div>');
			
			$this->view->getFormHtmlBuilder()->close();
		}
		
		$this->areaObject = null;
		$this->areaEditable = null;
	}
}

// interface PreviewModel {
	
// 	public function createPropertyPath(PropertyPath $propertyPath) {
		
// 	}
	
// }