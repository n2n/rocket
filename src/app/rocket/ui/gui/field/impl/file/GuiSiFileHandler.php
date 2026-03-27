<?php

namespace rocket\ui\gui\field\impl\file;


use rocket\ui\si\content\impl\SiFileHandler;
use rocket\ui\si\content\impl\SiFile;
use n2n\web\http\UploadDefinition;
use n2n\io\managed\impl\TmpFileManager;
use n2n\io\managed\impl\FileFactory;
use n2n\io\UploadedFileExceedsMaxSizeException;
use n2n\validation\lang\ValidationMessages;
use n2n\io\IncompleteFileUploadException;
use rocket\ui\si\content\impl\SiUploadResult;
use n2n\core\container\N2nContext;
use n2n\io\managed\File;
use n2n\util\type\CastUtils;
use n2n\io\managed\img\ImageFile;
use n2n\io\managed\img\ImageDimension;
use rocket\ui\gui\res\GuiResourceRegistry;
use rocket\ui\gui\res\GuiResourceController;
use rocket\ui\si\content\impl\SiImageDimension;
use n2n\io\managed\img\ThumbCut;
use n2n\util\StringUtils;
use n2n\io\managed\FileManager;
use n2n\io\managed\FileLocator;
use n2n\util\type\ArgUtils;

class GuiSiFileHandler implements SiFileHandler {

	public ImageDimensionsImportMode $imageDimensionsImportMode = ImageDimensionsImportMode::USED_ONLY ;
	/**
	 * @var ImageDimension[]
	 */
	public array $extraImageDimensions = [] {
		get => $this->extraImageDimensions;
		set (array $extraImageDimensions) {
			ArgUtils::valArray($extraImageDimensions, ImageDimension::class);
			$this->extraImageDimensions = $extraImageDimensions;
		}
	}

	public ?FileManager $targetFileManager = null;
	public ?FileLocator $targetFileLocator = null;

	function __construct(private GuiSiFileFactory $guiSiFileFactory, private GuiFileVerificator $fileVerificator) {
	}

	function upload(UploadDefinition $uploadDefinition, N2nContext $n2nContext): SiUploadResult {
		/**
		 * @var TmpFileManager $tmpFileManager
		 */
		$tmpFileManager = $n2nContext->lookup(TmpFileManager::class);

		$file = null;
		try {
			$file = FileFactory::createFromUploadDefinition($uploadDefinition);
		} catch (UploadedFileExceedsMaxSizeException $e) {
			return SiUploadResult::createError(ValidationMessages
					::uploadMaxSize($e->getMaxSize(), $uploadDefinition->getName(), $uploadDefinition->getSize())
					->t($n2nContext->getN2nLocale()));
		} catch (IncompleteFileUploadException $e) {
			return SiUploadResult::createError(ValidationMessages
					::uploadIncomplete($uploadDefinition->getName())
					->t($n2nContext->getN2nLocale()));
		}

		if (null !== ($message = $this->fileVerificator->validate($file))) {
			return SiUploadResult::createError($message->t($n2nContext->getN2nLocale()));
		}

		$tmpFileManager->add($file, $n2nContext->lookup(\n2n\web\http\HttpContext::class)->getSession());

		return SiUploadResult::createSuccess($file);
	}

	function createSiFile(File $file, N2nContext $n2nContext): SiFile {
		$siFile = $this->guiSiFileFactory->createSiFile($file, $n2nContext);

		if (!$this->guiSiFileFactory->imageRecognized) {
			return $siFile;
		}

		$imageDimensions = $this->determineImageDimensions($file);
		if (empty($imageDimensions)) {
			return $siFile;
		}

		$siFile->setImageDimensions($this->createSiImageDimensions(new ImageFile($file), $imageDimensions));
		return $siFile;
	}

	/**
	 * @param File $file
	 * @return ImageDimension[]
	 */
	function determineImageDimensions(File $file): array {
		$imageDimensions = array();

		if (!$file->getFileSource()->getAffiliationEngine()->hasThumbSupport()) {
			return $imageDimensions;
		}

		foreach ($this->extraImageDimensions as $imageDimension) {
			$imageDimensions[(string) $imageDimension] = $imageDimension;
		}

		$autoImageDimensions = array();
		switch ($this->imageDimensionsImportMode) {
			case ImageDimensionsImportMode::ALL:
				if ($this->targetFileManager !== null) {
					$autoImageDimensions = $this->targetFileManager->getPossibleImageDimensions($file, $this->targetFileLocator);
				}

				break;
			case ImageDimensionsImportMode::USED_ONLY:
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

	/**
	 * @param ImageFile $imageFile
	 * @param ImageDimension[] $imageDimensions
	 * @return SiImageDimension[]
	 */
	private function createSiImageDimensions(ImageFile $imageFile, array $imageDimensions): array {
		$siImageDimensions = [];
		foreach ($imageDimensions as $id => $imageDimension) {
			$thumbCut = $imageFile->getThumbCut($imageDimension);

			$imageDimension = ImageDimension::createFromString($imageDimension);
			$exits = true;
			if ($thumbCut === null) {
				$thumbCut = ThumbCut::auto($imageFile->getImageSource(), $imageDimension);
				$exits = false;
			}
			$ratioFixed = $imageDimension->isCropped();
			$idExt = $imageDimension->getIdExt();

			$siImageDimensions[] = new SiImageDimension($id, ($idExt !== null ? StringUtils::pretty($idExt) : null),
					$imageDimension->getWidth(), $imageDimension->getHeight(), $ratioFixed,
					$thumbCut, $exits);
		}
		return $siImageDimensions;
	}


	function determineFileByRawId(array $fileId, ?File $currentValue, N2nContext $n2nContext): ?File {
		$siFileId = SiFileId::parse($fileId);

		$file = $this->guiSiFileFactory->determineTmpFile($siFileId, $n2nContext);
		if ($file !== null) {
			return $file;
		}

		if ($currentValue !== null
				&& $currentValue->getFileSource()->getFileManagerName() === $siFileId->getFileManagerName()
				&& $currentValue->getFileSource()->getQualifiedName() === $siFileId->getQualifiedName()) {
			return $currentValue;
		}

		return null;
	}

	function applyThumbCuts(File $file, array $thumbCuts): void {
		if (empty($thumbCuts) || !$file->getFileSource()->isImage()) {
			return;
		}

		$imageFile = new ImageFile($file);

		foreach ($thumbCuts as $id => $thumbCut) {
			$imageDimension = ImageDimension::createFromString($id);

			$thumbFileSource = $file->getFileSource()->getAffiliationEngine()->getThumbManager()
					->getByDimension($imageDimension);
			if ($thumbFileSource !== null) {
				$thumbFileSource->delete();
			}

			$imageFile->setThumbCut($imageDimension, $thumbCut);
		}
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
