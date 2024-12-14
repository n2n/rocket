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

class GuiSiFileHandler implements SiFileHandler {
	private $eiu;

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

		$tmpFileManager->add($file, $n2nContext->getHttpContext()->getSession());

		return SiUploadResult::createSuccess($file);
	}

	function createSiFile(File $file, N2nContext $n2nContext): SiFile {
		return $this->guiSiFileFactory->createSiFile($file, $this->fileVerificator->isImageRecognized(), $n2nContext);
	}

	function determineFileByRawId(array $siFileId, ?File $currentValue, N2nContext $n2nContext): ?File {
		$fileId = SiFileId::parse($siFileId);

		$file = $this->guiSiFileFactory->determineTmpFile($fileId, $n2nContext);
		if ($file !== null) {
			return $file;
		}

		if ($currentValue !== null
				&& $currentValue->getFileSource()->getFileManagerName() === $fileId->getFileManagerName()
				&& $currentValue->getFileSource()->getQualifiedName() === $fileId->getQualifiedName()) {
			return $currentValue;
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
