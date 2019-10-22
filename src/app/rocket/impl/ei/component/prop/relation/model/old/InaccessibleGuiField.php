<?php
namespace rocket\impl\ei\component\prop\relation;


use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\manage\gui\field\GuiFieldDisplayable;
use rocket\ei\manage\gui\field\GuiFieldEditable;
use n2n\util\ex\UnsupportedOperationException;
use n2n\l10n\N2nLocale;
use rocket\ei\util\Eiu;
use rocket\si\content\SiField;

class InaccessibleGuiField implements GuiField, GuiFieldDisplayable {
	private $label;
	
	public function __construct(string $label) {
		$this->label = $label;
	}
	
	public function isReadOnly(): bool {
		return true;
	}

	public function getHtmlContainerAttrs(): array {
		return [];
	}

	public function getEditable(): GuiFieldEditable {
		throw new UnsupportedOperationException();
	}

	public function createOutSiField(Eiu $eiu): SiField {
		return $view->getL10nText('common_inaccessible_err', null, null, null, 'rocket');
	}

	public function getUiOutputLabel(N2nLocale $n2nLocale): string {
		return $this->label; 
	}

	public function getSiStructureType(): string {
		return null;
	}
	
	public function getDisplayable(): GuiFieldDisplayable {
		return $this;
	}


	
}