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
namespace rocket\spec\ei\component\field\impl\file;

use n2n\impl\web\ui\view\html\Link;
use rocket\spec\ei\manage\control\IconType;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\l10n\N2nLocale;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\persistence\orm\property\EntityProperty;
use rocket\spec\ei\component\field\impl\file\command\ThumbEiCommand;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
use n2n\reflection\ArgUtils;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use rocket\spec\ei\component\field\impl\adapter\DraftableEiFieldAdapter;
use n2n\io\orm\ManagedFileEntityProperty;
use n2n\io\orm\FileEntityProperty;
use n2n\reflection\CastUtils;
use n2n\io\managed\File;
use rocket\spec\ei\component\field\impl\file\conf\FileEiFieldConfigurator;
use n2n\io\managed\img\impl\ThSt;
use n2n\impl\web\dispatch\mag\model\FileMag;
use rocket\spec\ei\manage\EiObject;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\gui\FieldSourceInfo;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use n2n\impl\web\ui\view\html\HtmlBuilder;

class FileEiField extends DraftableEiFieldAdapter {
	const DIM_IMPORT_MODE_ALL = 'all';
	const DIM_IMPORT_MODE_USED_ONLY = 'usedOnly';
	
	private $thumbEiCommand;
	
	private $checkImageResourceMemory = true;
	private $allowedExtensions = array();
	private $imageDimensionsImportMode = null;
	private $extraImageDimensions = array();
	private $maxSize;
	
	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new FileEiFieldConfigurator($this);
	}
	
	public function getAllowedExtensions() {
		return $this->allowedExtensions;
	}
	
	public function setAllowedExtensions(array $allowedExtensions = null) {
		$this->allowedExtensions = $allowedExtensions;
	}
	
	public function getImageDimensionImportMode() {
		return $this->imageDimensionsImportMode;
	}
	
	public function setImageDimensionImportMode(string $imageDimensionImportMode = null) {
		$this->imageDimensionsImportMode = $imageDimensionImportMode;
	}
	
	public static function getImageDimensionImportModes(): array {
		return array(self::DIM_IMPORT_MODE_ALL, self::DIM_IMPORT_MODE_USED_ONLY);
	}

	public function getExtraImageDimensions() {
		return $this->extraImageDimensions;
	}

	public function setExtraImageDimensions(array $extraImageDimensions) {
		$this->extraImageDimensions = $extraImageDimensions;
	}
	
	public function setMaxSize(int $maxSize = null) {
		$this->maxSize = $maxSize;
	}
	
	public function getMaxSize() {
		return $this->maxSize;
	}

	public function isCheckImageMemoryEnabled(): bool {
		return $this->checkImageResourceMemory;
	}
	
	public function setCheckImageMemoryEnabled(bool $checkImageResourceMemory) {
		$this->checkImageResourceMemory = $checkImageResourceMemory;
	}
	
	public function setThumbEiCommand(ThumbEiCommand $thumbEiCommand) {
		$thumbEiCommand->setFileEiField($this);
		$this->thumbEiCommand = $thumbEiCommand;
	}
	
	public function getThumbEiCommand() {
		return $this->thumbEiCommand;
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof FileEntityProperty 
				|| $entityProperty instanceof ManagedFileEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('n2n\io\managed\File',
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
	
	
	public function createOutputUiComponent(HtmlView $view, FieldSourceInfo $fieldSourceInfo) {
		$html = $view->getHtmlBuilder();
		$file = $fieldSourceInfo->getValue();
		
		if ($file === null) {
			return null;
		}

		CastUtils::assertTrue($file instanceof File);
		
		if (!$file->isValid()) {
			return $html->getEsc('[missing file]');
		} 
		
		if ($file->getFileSource()->isImage()) {
			return $this->createImageUiComponent($view, $fieldSourceInfo, $file);
		} 
		
		if ($file->getFileSource()->isHttpaccessible()) {
			return new Link($file->getFileSource()->getUrl(), $html->getEsc($file->getOriginalName()), array('target' => '_blank'));
		}
		
		return $html->getEsc($file->getOriginalName());
	}
	
	private function createImageUiComponent(HtmlView $view, FieldSourceInfo $entrySourceInfo, File $file) {
		$html = $view->getHtmlBuilder();
		
		$meta = $html->meta();
		$meta->addCss('js/thirdparty/colorbox/colorbox.css', 'screen');
		$meta->addJs('js/thirdparty/colorbox/jquery.colorbox-min.js');
		$meta->addJs('js/image-preview.js');
		
		$uiComponent = new HtmlElement('div', null, new Link($file->getFileSource()->getUrl(),
				$html->getImage($file, ThSt::crop(40, 28, true),
						array('title' => $file->getOriginalName())), array('class' => 'rocket-image-previewable')));
		
		if ($this->isThumbCreationEnabled($file) && !$entrySourceInfo->isNew()) {
			$httpContext = $view->getHttpContext();
			$uiComponent->appendContent($html->getLink(
					$httpContext->getControllerContextPath($entrySourceInfo->getEiState()->getControllerContext())
							->ext($this->thumbEiCommand->getId(), $entrySourceInfo->getEntryIdRep())
							->toUrl(array('refPath' => (string) $entrySourceInfo->getEiState()->getCurrentUrl($httpContext))),
					new HtmlElement('i', array('class' => IconType::ICON_CROP), ''),
					array('title' => $view->getL10nText('ei_impl_resize_image'),
							'class' => 'rocket-control rocket-simple-controls')));
		}
		
		return $uiComponent;
	}
	
	private function isThumbCreationEnabled(File $file) {
		if ($this->thumbEiCommand === null 
				|| !$file->getFileSource()->isThumbSupportAvailable()) return false;
		
		if (!empty($this->extraImageDimensions)) return true;
		
		$thumbEngine = $file->getFileSource()->getFileSourceThumbEngine();
		switch ($this->imageDimensionsImportMode) {
			case self::DIM_IMPORT_MODE_ALL:
				return !empty($thumbEngine->getPossibleImageDimensions());
			case self::DIM_IMPORT_MODE_USED_ONLY:
				return !empty($thumbEngine->getUsedImageDimensions());
			default:
				return false;
		}
	}
	
	public function createMag(string $propertyName, FieldSourceInfo $entrySourceInfo): Mag {
		$allowedExtensions = $this->getAllowedExtensions();
		return new FileMag($propertyName, $this->getLabelLstr(), (sizeof($allowedExtensions) ? $allowedExtensions : null), 
				$this->isCheckImageMemoryEnabled(), null, 
				$this->isMandatory($entrySourceInfo));
	}

	public function isStringRepresentable(): bool {
		return true;
	}
	
	public function buildIdentityString(EiObject $eiObject, N2nLocale $n2nLocale) {
		return $this->getPropertyAccessProxy()->getValue($eiObject->getObject());
	}
}
