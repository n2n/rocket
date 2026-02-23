<?php

namespace rocket\ui\gui\field\impl\file;

use rocket\ui\gui\field\impl\InGuiFieldAdapter;
use rocket\ui\gui\field\GuiField;
use rocket\ui\si\content\SiFieldModel;
use n2n\core\container\N2nContext;
use rocket\ui\si\content\impl\FileInSiField;
use n2n\io\managed\File;
use n2n\io\managed\FileLocator;
use n2n\io\managed\FileManager;

class FileInGuiField extends InGuiFieldAdapter implements GuiField, SiFieldModel {

	private GuiSiFileHandler $guiSiFileHandler;

	function __construct(private readonly FileInSiField $siField, bool $imageRecognized = true) {
		parent::__construct($siField);
		$this->siField->setFileHandler($this->guiSiFileHandler = new GuiSiFileHandler(new GuiSiFileFactory($imageRecognized),
				new GuiFileVerificator($siField, $imageRecognized)));
	}

	function setValue(?File $file): static {
		$this->siField->setValue($file);
		return $this;
	}

	function getValue(): ?File {
		return $this->siField->getValue();
	}

	function setExtraImageDimensions(array $extraImageDimensions): static {
		$this->guiSiFileHandler->extraImageDimensions = $extraImageDimensions;
		return $this;
	}

	function setImageDimensionsImportMode(ImageDimensionsImportMode $imageDimensionsImportMode): static {
		$this->guiSiFileHandler->imageDimensionsImportMode = $imageDimensionsImportMode;
		return $this;
	}

	function setTargetFileLocator(?FileLocator $fileLocator): static {
		$this->guiSiFileHandler->targetFileLocator = $fileLocator;
		return $this;
	}

	function setTargetFileManager(?FileManager $fileManager): static {
		$this->guiSiFileHandler->targetFileManager = $fileManager;
		return $this;
	}

	protected function createInputMappers(N2nContext $n2nContext): array {
		return [];
	}
}