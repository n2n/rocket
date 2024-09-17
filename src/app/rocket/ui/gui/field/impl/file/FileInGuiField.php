<?php

namespace rocket\ui\gui\field\impl\file;

use rocket\ui\gui\field\impl\InGuiFieldAdapter;
use rocket\ui\gui\field\GuiField;
use rocket\ui\si\content\SiFieldModel;
use n2n\core\container\N2nContext;
use rocket\ui\si\content\impl\FileInSiField;
use n2n\io\managed\File;
use n2n\io\managed\FileLocator;

class FileInGuiField extends InGuiFieldAdapter implements GuiField, SiFieldModel {

	private GuiSiFileFactory $guiSiFileFactory;

	function __construct(private readonly FileInSiField $siField) {
		$this->siField->setModel($this);
		$this->siField->setFileHandler(new GuiSiFileHandler($this->guiSiFileFactory = new GuiSiFileFactory()));
	}

	function setValue(?File $file): static {
		$this->siField->setValue($file);
		return $this;
	}

	function getValue(): ?File {
		return $this->siField->getValue();
	}

	function setExtraImageDimensions(array $extraImageDimensions): static {
		$this->guiSiFileFactory->setExtraImageDimensions($extraImageDimensions);
	}

	function setImageDimensionsImportMode(ImageDimensionsImportMode $imageDimensionsImportMode): static {
		$this->guiSiFileFactory->setImageDimensionsImportMode($imageDimensionsImportMode);
	}

	function setTargetFileLocator(?FileLocator $fileLocator): static {
		$this->guiSiFileFactory->setTargetFileLocator($fileLocator);
	}

	protected function createInputMappers(N2nContext $n2nContext): array {
		// TODO: Implement createInputMappers() method.
	}
}