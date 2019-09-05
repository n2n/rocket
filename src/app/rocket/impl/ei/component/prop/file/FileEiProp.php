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

use n2n\l10n\N2nLocale;
use n2n\persistence\orm\property\EntityProperty;
use n2n\util\type\ArgUtils;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\TypeConstraint;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use n2n\io\orm\ManagedFileEntityProperty;
use n2n\io\orm\FileEntityProperty;
use n2n\util\type\CastUtils;
use n2n\io\managed\File;
use rocket\impl\ei\component\prop\file\conf\FileEiPropConfigurator;
use n2n\web\http\Session;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use n2n\io\managed\impl\TmpFileManager;
use rocket\ei\EiPropPath;
use rocket\si\content\SiField;
use rocket\si\content\impl\SiFields;
use rocket\impl\ei\component\prop\file\conf\ThumbResolver;
use n2n\web\http\UploadDefinition;
use n2n\io\managed\impl\FileFactory;
use n2n\io\UploadedFileExceedsMaxSizeException;
use n2n\io\IncompleteFileUploadException;
use n2n\validation\impl\ValidationMessages;
use rocket\si\content\impl\SiFile;
use rocket\si\content\impl\SiUploadResult;
use rocket\si\content\impl\SiFileHandler;
use rocket\impl\ei\component\prop\file\conf\FileId;
use rocket\si\content\impl\FileInSiField;
use rocket\ei\manage\entry\EiFieldValidationResult;
use n2n\io\managed\val\ValidationMessagesFile;
use n2n\validation\impl\ValidationUtils;

class FileEiProp extends DraftablePropertyEiPropAdapter {
	
	private $checkImageResourceMemory = true;
	private $allowedExtensions = [];
	private $allowedMimeTypes = [];
	private $maxSize;
	private $namingEiPropPath;
	/**
	 * @var ThumbResolver|null
	 */
	private $thumbResolver;
	
	function __construct() {
		parent::__construct();
		
		$this->thumbResolver = new ThumbResolver();
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		return new FileEiPropConfigurator($this);
	}
	
	public function getThumbResolver() {
		return $this->thumbResolver;
	}
	
	public function setThumbResolver(ThumbResolver $thumbResolver) {
		$this->thumbResolver = $thumbResolver;
	}
	
	public function getAllowedExtensions() {
		return $this->allowedExtensions;
	}
	
	public function setAllowedExtensions(array $allowedExtensions) {
		$this->allowedExtensions = $allowedExtensions;
	}
	
	public function getAllowedMimeTypes() {
		return $this->allowedMimeTypes;
	}
	
	public function setAllowedMimeTypes(array $allowedMimeTypes) {
		$this->allowedMimeTypes = $allowedMimeTypes;
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
	
	public function testEiFieldValue(Eiu $eiu, $eiFieldValue): bool {
		if (!parent::testEiFieldValue($eiu, $eiFieldValue)) {
			return false;
		}
		
		return $eiFieldValue !== null && $this->testType($eiFieldValue);
	}
	
	
	public function validateEiFieldValue(Eiu $eiu, $eiFieldValue, EiFieldValidationResult $validationResult) {
		parent::validateEiFieldValue($eiu, $eiFieldValue, $validationResult);
		
		$file = $eiFieldValue;
		
		if ($file === null) {
			return;
		}
		
		CastUtils::assertTrue($file instanceof File);
		
		if (!$this->testType($file)) {
			$validationResult->addError(ValidationMessages::fileType($file,
					array_merge($this->allowedExtensions, $this->allowedMimeTypes)));
		}
	}
	
	/**
	 * @return boolean
	 */
	private function testType($file) {
		return ValidationUtils::isFileTypeSupported($file,
				(empty($this->allowedMimeTypes) ? null : $this->allowedMimeTypes),
				(empty($this->allowedExtensions) ? null : $this->allowedExtensions));
	}
	
	public function createOutSiField(Eiu $eiu): SiField {
		$siFile = $this->buildSiFileFromEiu($eiu);
		
		return SiFields::fileOut($siFile);
		
// 		if (!$file->isValid()) {
// 			return $html->getEsc('[missing file]');
// 		} 
		
// 		if ($file->getFileSource()->isImage()) {
// 			return $this->createImageUiComponent($view, $eiu, $file);
// 		} 
		
// 		$url = $this->createFileUrl($file, $eiu);
// // 		if ($file->getFileSource()->isHttpaccessible()) {
// 			return new Link($url, $html->getEsc($file->getOriginalName()), array('target' => '_blank'));
// // 		}
		
// // 		return $html->getEsc($file->getOriginalName());
	}
	
// 	private function createFileUrl(File $file, Eiu $eiu) {
// 		if ($file->getFileSource()->isHttpaccessible()) {
// 			return $file->getFileSource()->getUrl();
// 		}
		
// 		return $eiu->frame()->getUrlToCommand($this->thumbEiCommand)->extR(['preview', $eiu->entry()->getPid()]);
// 	}
	
// 	private function createImageUiComponent(HtmlView $view, Eiu $eiu, File $file) {
// 		$html = $view->getHtmlBuilder();
		
// 		$meta = $html->meta();
// 		$html->meta()->addCss('impl/js/thirdparty/magnific-popup/magnific-popup.min.css', 'screen');
// 		$html->meta()->addJs('impl/js/thirdparty/magnific-popup/jquery.magnific-popup.min.js');
// 		$meta->addJs('impl/js/image-preview.js');
		
// 		$uiComponent = new HtmlElement('div', 
// 				array('class' => 'rocket-simple-commands'), 
// 				new Link($this->createFileUrl($file, $eiu), 
// 						$html->getImage($file, ThSt::crop(40, 30, true), array('title' => $file->getOriginalName())), 
// 						array('class' => 'rocket-image-previewable')));
		
// 		if ($this->isThumbCreationEnabled($file) && !$eiu->entry()->isNew()) {
// 			$httpContext = $view->getHttpContext();
// 			$uiComponent->appendContent($html->getLink($eiu->frame()->getUrlToCommand($this->thumbEiCommand)
// 					->extR($eiu->entry()->getPid(), array('refPath' => (string) $eiu->frame()->getEiFrame()->getCurrentUrl($httpContext))),
// 					new HtmlElement('i', array('class' => SiIconType::ICON_CROP), ''),
// 					array('title' => $view->getL10nText('ei_impl_resize_image'),
// 							'class' => 'btn btn-secondary', 'data-jhtml' => 'true')));
// 		}
		
// 		return $uiComponent;
// 	}
	
	
	
	public function createInSiField(Eiu $eiu): SiField {
		$siFile = $this->buildSiFileFromEiu($eiu);
		
		return SiFields::fileIn($siFile, $eiu->frame()->getApiUrl(), $eiu->guiField()->createCallId(), 
						new SiFileHanlderImpl($eiu, $this->thumbResolver))
				->setMandatory($this->isMandatory($eiu))
				->setMaxSize($this->maxSize)
				->setAcceptedExtensions($this->allowedExtensions)
				->setAcceptedMimeTypes($this->allowedMimeTypes);
		
// 		$allowedExtensions = $this->getAllowedExtensions();
// 		return new FileMag($this->getLabelLstr(), (sizeof($allowedExtensions) ? $allowedExtensions : null), 
// 				$this->isCheckImageMemoryEnabled(), null, 
// 				$this->isMandatory($eiu));
	}

	function saveSiField(SiField $siField, Eiu $eiu) {
		CastUtils::assertTrue($siField instanceof FileInSiField);
		
		$siFile = $siField->getValue();
		if ($siFile === null) {
			$eiu->field()->setValue(null);
			return;
		}
		
		$fileId = $siFile->getId();
		CastUtils::assertTrue($fileId instanceof FileId);
		
		$eiu->field()->setValue($this->thumbResolver->determineFile($fileId, $eiu));
	}
	
	/**
	 * @param Eiu $eiu
	 * @return SiFile|null
	 */
	private function buildSiFileFromEiu(Eiu $eiu) {
		$file = $eiu->field()->getValue();
		if ($file === null) {
			return null;
		}
		
		CastUtils::assertTrue($file instanceof File);
		return $this->thumbResolver->createSiFile($file, $eiu);
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

class SiFileHanlderImpl implements SiFileHandler {
	private $eiu;
	private $thumbResolver;
	
	function __construct(Eiu $eiu, FileEiProp $fileEiProp, ThumbResolver $thumbResolver) {
		$this->eiu = $eiu;
		$this->fileEiProp = $fileEiProp
		$this->thumbResolver = $thumbResolver;
	}
	
	function upload(UploadDefinition $uploadDefinition): SiUploadResult {
		/**
		 * @var TmpFileManager $tmpFileManager
		 */
		$tmpFileManager = $this->eiu->lookup(TmpFileManager::class);
		
		$file = null;
		try {
			$file = FileFactory::createFromUploadDefinition($uploadDefinition);
		} catch (UploadedFileExceedsMaxSizeException $e) {
			return SiUploadResult::createError(ValidationMessages
					::uploadMaxSize($e->getMaxSize(), $uploadDefinition->getName(), $uploadDefinition->getSize())
					->t($this->n2nContext->getN2nLocale()));
		} catch (IncompleteFileUploadException $e) {
			return SiUploadResult::createError(ValidationMessages
					::uploadIncomplete($uploadDefinition->getName())
					->t($this->n2nContext->getN2nLocale()));
		}
		
		
		
		/* $qualifiedName = */ $tmpFileManager->add($file, $this->eiu->getN2nContext()->getHttpContext()->getSession());
				
		return SiUploadResult::createSuccess($this->thumbResolver->createSiFile($file, $this->eiu));
	}
	
	function getSiFileByRawId(array $rawId): ?SiFile {
		$fileId = FileId::parse($rawId);
		
		$file = $this->thumbResolver->determineFile($fileId, $this->eiu);
		if ($file !== null) {
			return $this->thumbResolver->createSiFile($file, $this->eiu);
		}
		
		return null;
	}
	
// 	function createTmpSiFile(File $file, string $qualifiedName) {
// 		$siFile = new SiFile($file->getOriginalName(), $this->thumbResolver->createTmpUrl($this->eiu, $qualifiedName));
		
// 		if (null !== ($this->thumbResolver->buildThumbFile($file))) {
// 			$siFile->setThumbUrl($this->thumbResolver->createTmpThumbUrl($this->eiu, $qualifiedName,
// 					SiFile::getThumbStrategy()->getImageDimension()));
// 		}
		
// 		return $siFile;
// 	}
}
