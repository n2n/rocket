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
namespace rocket\impl\ei\component\prop\file;

use n2n\impl\web\ui\view\html\Link;
use rocket\ei\manage\control\IconType;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\l10n\N2nLocale;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\persistence\orm\property\EntityProperty;
use rocket\impl\ei\component\prop\file\command\ThumbEiCommand;
use n2n\util\type\ArgUtils;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\TypeConstraint;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use n2n\io\orm\ManagedFileEntityProperty;
use n2n\io\orm\FileEntityProperty;
use n2n\util\type\CastUtils;
use n2n\io\managed\File;
use rocket\impl\ei\component\prop\file\conf\FileEiPropConfigurator;
use n2n\io\managed\img\impl\ThSt;
use n2n\impl\web\dispatch\mag\model\FileMag;
use n2n\web\dispatch\mag\Mag;
use n2n\web\http\Session;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use n2n\io\managed\impl\TmpFileManager;
use rocket\ei\EiPropPath;

class FileEiProp extends DraftablePropertyEiPropAdapter {
	const DIM_IMPORT_MODE_ALL = 'all';
	const DIM_IMPORT_MODE_USED_ONLY = 'usedOnly';
	
	private $thumbEiCommand;
	
	private $checkImageResourceMemory = true;
	private $allowedExtensions = array();
	private $imageDimensionsImportMode = null;
	private $extraImageDimensions = array();
	private $maxSize;
	private $namingEiPropPath;
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		return new FileEiPropConfigurator($this);
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
		$thumbEiCommand->setFileEiProp($this);
		$this->thumbEiCommand = $thumbEiCommand;
	}
	
	public function getThumbEiCommand() {
		return $this->thumbEiCommand;
	}
		
	/**
	 * @param EiPropPath $eiPropPath
	 */
	public function setNamingEiPropPath(?EiPropPath $eiPropPath) {
		$this->namingEiPropPath = $eiPropPath;
	}
	
	/**
	 * @return \rocket\ei\EiPropPath|null
	 */
	public function getNamingEiPropPath() {
		return $this->namingEiPropPath;
	}
	
// 	public function setMultiUploadEiCommand(MultiUploadEiCommand $multiUploadEiCommand) {
// 		$this->multiUploadEiCommand = $multiUploadEiCommand;
// 	}
	
// 	public function getMultiUploadEiCommand() {
// 		return $this->multiUploadEiCommand;
// 	}
	
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
	
	
	public function createUiComponent(HtmlView $view, Eiu $eiu) {
		$html = $view->getHtmlBuilder();
		$file = $eiu->field()->getValue();
		
		if ($file === null) {
			return null;
		}

		CastUtils::assertTrue($file instanceof File);
		
		if (!$file->isValid()) {
			return $html->getEsc('[missing file]');
		} 
		
		if ($file->getFileSource()->isImage()) {
			return $this->createImageUiComponent($view, $eiu, $file);
		} 
		
		$url = $this->createFileUrl($file, $eiu);
// 		if ($file->getFileSource()->isHttpaccessible()) {
			return new Link($url, $html->getEsc($file->getOriginalName()), array('target' => '_blank'));
// 		}
		
// 		return $html->getEsc($file->getOriginalName());
	}
	
	private function createFileUrl(File $file, Eiu $eiu) {
		if ($file->getFileSource()->isHttpaccessible()) {
			return $file->getFileSource()->getUrl();
		}
		
		return $eiu->frame()->getUrlToCommand($this->thumbEiCommand)->extR(['preview', $eiu->entry()->getPid()]);
	}
	
	private function createImageUiComponent(HtmlView $view, Eiu $eiu, File $file) {
		$html = $view->getHtmlBuilder();
		
		$meta = $html->meta();
		$html->meta()->addCss('impl/js/thirdparty/magnific-popup/magnific-popup.min.css', 'screen');
		$html->meta()->addJs('impl/js/thirdparty/magnific-popup/jquery.magnific-popup.min.js');
		$meta->addJs('impl/js/image-preview.js');
		
		$uiComponent = new HtmlElement('div', 
				array('class' => 'rocket-simple-commands'), 
				new Link($this->createFileUrl($file, $eiu), 
						$html->getImage($file, ThSt::crop(40, 30, true), array('title' => $file->getOriginalName())), 
						array('class' => 'rocket-image-previewable')));
		
		if ($this->isThumbCreationEnabled($file) && !$eiu->entry()->isNew()) {
			$httpContext = $view->getHttpContext();
			$uiComponent->appendContent($html->getLink($eiu->frame()->getUrlToCommand($this->thumbEiCommand)
					->extR($eiu->entry()->getPid(), array('refPath' => (string) $eiu->frame()->getEiFrame()->getCurrentUrl($httpContext))),
					new HtmlElement('i', array('class' => IconType::ICON_CROP), ''),
					array('title' => $view->getL10nText('ei_impl_resize_image'),
							'class' => 'btn btn-secondary', 'data-jhtml' => 'true')));
		}
		
		return $uiComponent;
	}
	
	private function isThumbCreationEnabled(File $file) {
		if ($this->thumbEiCommand === null 
				|| !$file->getFileSource()->getVariationEngine()->hasThumbSupport()) return false;
		
		if (!empty($this->extraImageDimensions)) return true;
		
		$thumbEngine = $file->getFileSource()->getThumbManager();
		switch ($this->imageDimensionsImportMode) {
			case self::DIM_IMPORT_MODE_ALL:
				return !empty($thumbEngine->getPossibleImageDimensions());
			case self::DIM_IMPORT_MODE_USED_ONLY:
				return !empty($thumbEngine->getUsedImageDimensions());
			default:
				return false;
		}
	}
	
	public function createMag(Eiu $eiu): Mag {
		$allowedExtensions = $this->getAllowedExtensions();
		return new FileMag($this->getLabelLstr(), (sizeof($allowedExtensions) ? $allowedExtensions : null), 
				$this->isCheckImageMemoryEnabled(), null, 
				$this->isMandatory($eiu));
	}

	public function isStringRepresentable(): bool {
		return true;
	}
	
	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		$file = $eiu->object()->readNativValue($this);
		if ($file === null) return null;
		
		CastUtils::assertTrue($file instanceof File);
		
		if (!$file->isValid()) return (string) $file;
		
		return $file->getOriginalName();
	}
	
	public function copy(Eiu $eiu, $value, Eiu $copyEiu) {
		if ($value === null) return null;

		CastUtils::assertTrue($value instanceof File);
		if (!$value->isValid()) return null;
		
		$tmpFileManager = $copyEiu->lookup(TmpFileManager::class);
		CastUtils::assertTrue($tmpFileManager instanceof TmpFileManager);
		
		return $tmpFileManager->createCopyFromFile($value, $copyEiu->lookup(Session::class, false));
	}
}
