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

use n2n\io\managed\File;
use n2n\io\managed\impl\TmpFileManager;
use n2n\util\type\ArgUtils;
use n2n\util\type\CastUtils;
use n2n\util\type\TypeConstraints;
use n2n\web\http\Session;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\entry\EiFieldValidationResult;
use rocket\op\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropNatureAdapter;
use rocket\ui\gui\field\impl\file\SiFileId;
use rocket\impl\ei\component\prop\file\conf\FileVerificator;
use rocket\ui\gui\field\impl\file\ThumbResolver;
use rocket\ui\si\content\impl\FileInSiField;
use rocket\ui\si\content\impl\SiFile;
use rocket\si\content\impl\SiUploadResult;
use n2n\io\managed\img\ImageFile;
use n2n\io\managed\img\ImageDimension;
use rocket\op\ei\manage\idname\IdNameProp;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\ui\gui\field\BackableGuiField;
use rocket\ui\gui\field\impl\GuiFields;

class FileEiPropNature extends DraftablePropertyEiPropNatureAdapter {

	/**
	 * @var EiPropPath|null
	 */
	private $namingEiPropPath;
	/**
	 * @var ThumbResolver
	 */
	private $thumbResolver;
	/**
	 * @var FileVerificator
	 */
	private $fileVerificator;
	
	
	function __construct(PropertyAccessProxy $propertyAccessProxy) {
		parent::__construct($propertyAccessProxy->createRestricted(TypeConstraints::namedType(File::class, true)));
		
//		$this->thumbResolver = new ThumbResolver();
		$this->fileVerificator = new FileVerificator();
	}

	function setup(Eiu $eiu): void {
//		$thumbEiCmdPath = $eiu->mask()->addCmd(new ThumbNatureEiCommand($eiu->prop()->getPath()))->getEiCmdPath();
//		$this->thumbResolver->setThumbEiCmdPath($thumbEiCmdPath);
	}

	/**
	 * @return ThumbResolver
	 */
	public function getThumbResolver() {
		return $this->thumbResolver;
	}
	
	/**
	 * @param ThumbResolver $thumbResolver
	 */
	public function setThumbResolver(ThumbResolver $thumbResolver) {
		$this->thumbResolver = $thumbResolver;
	}
	
	/**
	 * @return FileVerificator
	 */
	public function getFileVerificator() {
		return $this->fileVerificator;
	}
	
	/**
	 * @param FileVerificator $fileVerificator
	 */
	public function setFileVerificator(FileVerificator $fileVerificator) {
		$this->fileVerificator = $fileVerificator;
	}
		

// 	public function setMultiUploadEiCommand(MultiUploadEiCommand $multiUploadEiCommand) {
// 		$this->multiUploadEiCommand = $multiUploadEiCommand;
// 	}
	
// 	public function getMultiUploadEiCommand() {
// 		return $this->multiUploadEiCommand;
// 	}
	
//	public function setEntityProperty(?EntityProperty $entityProperty): void {
//		ArgUtils::assertTrue($entityProperty instanceof ManagedFileEntityProperty);
//		$this->entityProperty = $entityProperty;
//	}
	
// 	protected function isEntityProperty(?EntityProperty $entityProperty) {
// 		$entityProperty instanceof FileEntityProperty;
// 	}
	

	
	public function testEiFieldValue(Eiu $eiu, $eiFieldValue): bool {
		if (!parent::testEiFieldValue($eiu, $eiFieldValue)) {
			return false;
		}
		
		if ($eiFieldValue === null) {
			return true;
		}
		
		\InvalidArgumentException::assertTrue($eiFieldValue instanceof File);
		return $this->fileVerificator->test($eiFieldValue);
	}
	
	public function validateEiFieldValue(Eiu $eiu, $file, EiFieldValidationResult $validationResult) {
		parent::validateEiFieldValue($eiu, $file, $validationResult);
		
		if ($file === null) {
			return;
		}
		
		ArgUtils::assertTrue($file instanceof File);
		
		if (null !== ($message = $this->fileVerificator->validate($file))) {
			$validationResult->addError($message);
		}
	}
	
	function buildOutGuiField(Eiu $eiu): ?BackableGuiField {
//		$siFile = $this->buildSiFileFromEiu($eiu);
		
		return GuiFields::fileOut($eiu->field()->getValue());
		
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
	
	public function buildInGuiField(Eiu $eiu): ?BackableGuiField {
		$siFile = $this->buildSiFileFromEiu($eiu);
		
//		$siField = SiFields::fileIn($siFile, $eiu->frame()->getApiUrl(), $eiu->guiField()->createCallId())
//				->setMandatory($this->isMandatory())
//				->setMaxSize($this->fileVerificator->getMaxSize())
//				->setAcceptedExtensions($this->fileVerificator->getAllowedExtensions())
//				->setAcceptedMimeTypes($this->fileVerificator->getAllowedMimeTypes())
//				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());

		return GuiFields::fileIn($this->isMandatory())
				->setValue($eiu->field()->getValue())
				->setModel($eiu->field()->asGuiFieldModel());

//		return $eiu->factory()->newGuiField($siField)
//				->setSaver(function () use ($siField, $eiu) {
//					$this->saveSiField($siField, $eiu);
//				})
//				->toGuiField();
		
// 		$allowedExtensions = $this->getAllowedExtensions();
// 		return new FileMag($this->getLabelLstr(), (sizeof($allowedExtensions) ? $allowedExtensions : null), 
// 				$this->isImageRecognized(), null, 
// 				$this->isMandatory($eiu));
	}

	function determineImageDimensions(File $file) {
		$imageDimensions = array();

		if (!$file->getFileSource()->getAffiliationEngine()->hasThumbSupport()) {
			return $imageDimensions;
		}

		foreach ($this->extraImageDimensions as $imageDimension) {
			$imageDimensions[(string) $imageDimension] = $imageDimension;
		}

		$autoImageDimensions = array();
		switch ($this->imageDimensionsImportMode) {
			case self::DIM_IMPORT_MODE_ALL:
				if ($this->targetFileManager !== null) {
					$autoImageDimensions = $this->targetFileManager->getPossibleImageDimensions($file, $this->targetFileLocator);
				}

				break;
			case self::DIM_IMPORT_MODE_USED_ONLY:
				$thumbEngine = $file->getFileSource()->getAffiliationEngine()->getThumbManager();
				$autoImageDimensions = $thumbEngine->getUsedImageDimensions();
				break;
		}

		$rocketImageDimensionStr = (string) SiFile::getThumbStrategy()->getImageDimension();

		foreach ($autoImageDimensions as $autoImageDimension) {
			$autoImageDimensionStr = (string) $autoImageDimension;

			if ($autoImageDimensionStr == $rocketImageDimensionStr) {
				continue;
			}

			$imageDimensions[$autoImageDimensionStr] = $autoImageDimension;
		}

		return $imageDimensions;
	}

	function saveSiField(FileInSiField $siField, Eiu $eiu) {
		
		$siFile = $siField->getValue();
		if ($siFile === null) {
			$eiu->field()->setValue(null);
			return;
		}
		
		$fileId = $siFile->getId();
		CastUtils::assertTrue($fileId instanceof SiFileId);
		
		$eiu->field()->setValue($file = $this->thumbResolver->determineFile($fileId, $eiu));
		
		$siImageDimensions = $siFile->getImageDimensions();
		if (empty($siImageDimensions) || !$file->getFileSource()->isImage()) {
			return;
		}
		
		$imageFile = new ImageFile($file);
		
		foreach ($siImageDimensions as $siImageDimension) {
			$imageDimension = ImageDimension::createFromString($siImageDimension->getId());
			
			$thumbFileSource = $file->getFileSource()->getAffiliationEngine()->getThumbManager()
					->getByDimension($imageDimension);
			if ($thumbFileSource !== null) {
				$thumbFileSource->delete();
			}
			
			$imageFile->setThumbCut($imageDimension, $siImageDimension->getThumbCut());
		}
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
		return $this->thumbResolver->createSiFile($file, $eiu, $this->fileVerificator->isImageRecognized());
	}
	
	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			return $this->buildIdentityString($eiu);
		})->toIdNameProp();
	}
	
	private function buildIdentityString(Eiu $eiu) {
		$file = $eiu->object()->readNativeValue($eiu->prop()->getEiProp());
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
