<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\spec\ei\preview;

use n2n\web\ui\Raw;
use n2n\web\ui\UiUtils;
use n2n\web\dispatch\ui\Form;
use n2n\persistence\orm\OrmUtils;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\l10n\N2nLocale;

use rocket\spec\ei\component\UnknownEiComponentException;

class PreviewHtmlBuilder {
	private $view;
	private $previewModel;
	private $entryModel;
	private $eiState;
	private $eiSelection;
	private $n2nLocale;
	private $eiSpec;
	private $areaObject;
	private $areaEditable;
	
	public function __construct(HtmlView $view, PreviewModel $previewModel = null, N2nLocale $n2nLocale = null) {
		$this->view = $view;
		$this->previewModel = $previewModel;
		$this->n2nLocale = $n2nLocale;
		
		if (isset($previewModel)) {
			$this->eiState = $this->previewModel->getEiState();
			$this->entryModel = $previewModel->getEntryModel();
			$this->eiSpec = $this->entryModel->getEiSpec();
		}
		
		// $this->view->getHtmlBuilder()->addJs('js/preview-inpage.js', 'rocket');
		$this->view->getHtmlBuilder()->addCss('css/preview-inpage.css', null, 'rocket');
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
						
			$eiSelection = $this->entryModel->getEiSelection();
			$areaObjectId = OrmUtils::extractId($areaObject, $this->eiSpec->getEntityModel());
			if ($eiSelection->getId() != $areaObjectId) {
				$this->areaEditable = false;
				$this->areaObject = $areaObject;
				return;
			}
		} else if (is_null($this->entryModel)) {
			$this->view->throwRuntimeException(
					new PreviewAreaException('No object given'));
		}
		
		$this->areaObject = $this->entryModel->getEiSelection()->getEntityObj();
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
		
		$eiField = null;
		try {
			$eiField = $this->eiSpec->getEiFieldByPropertyName($propertyName);
		} catch (UnknownEiComponentException $e) {
			$this->view->throwRuntimeException(
					UiUtils::createCouldNotRenderUiComponentException($e));
		}
		
		if (!($eiField instanceof PreviewableEiField)) {
			$this->view->throwRuntimeException(
					new PreviewAreaException('EiField \'' . get_class($eiField) . '\' is not previewable.'));
		}
		
		if ($this->entryModel->containsPropertyName($propertyName)) {
			return $eiField->createEditablePreviewUiComponent($this->previewModel, 
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
			$eiSelection = $this->entryModel->getEiSelection();
			
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
