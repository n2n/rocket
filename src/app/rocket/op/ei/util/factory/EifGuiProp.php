<?php
namespace rocket\op\ei\util\factory;

use rocket\impl\ei\component\prop\adapter\gui\EiGuiPropProxy;
use rocket\op\ei\manage\gui\EiGuiProp;
use rocket\ui\gui\GuiProp;
use rocket\op\ei\manage\gui\DisplayDefinition;
use Closure;
use rocket\ui\si\meta\SiStructureType;
use rocket\op\ei\manage\gui\EiGuiField;
use n2n\util\type\ArgUtils;
use rocket\ui\gui\SimpleEiGuiProp;

class EifGuiProp {
	private $siStructureType = SiStructureType::ITEM;
	private $defaultDisplayed = true;
	private $overwriteLabel = null;
	private $overwriteHelpText = null;

	/**
	 * @param string $label
	 * @param string|null $helpText
	 * @param Closure $guiFieldCallback
	 */
	function __construct(private string $label, private ?string $helpText, private Closure $guiFieldCallback) {
	}

	/**
	 * @param string $siStructureType
	 * @return EifGuiProp
	 */
	function setSiStructureType(string $siStructureType): static {
		ArgUtils::valEnum($siStructureType, SiStructureType::all());
		$this->siStructureType = $siStructureType;
		return $this;
	}

	/**
	 * @return string
	 */
	function getSiStructureType(): string {
		return $this->siStructureType;
	}

	/**
	 * @param bool $defaultDisplayed
	 * @return EifGuiProp
	 */
	function setDefaultDisplayed(bool $defaultDisplayed): static {
		$this->defaultDisplayed = $defaultDisplayed;
		return $this;
	}

	/**
	 * @return bool
	 */
	function isDefaultDisplayed(): bool {
		return $this->defaultDisplayed;
	}

	function setOverwriteLabel(?string $overwriteLabel): static {
		$this->overwriteLabel = $overwriteLabel;
		return $this;
	}

	/**
	 * @return string|null
	 */
	function getOverwriteLabel(): ?string {
		return $this->overwriteLabel;
	}

//	function toGuiProp() {
//		$displayDefinition = new DisplayDefinition($this->siStructureType, $this->defaultDisplayed,
//				$this->overwriteLabel, $this->overwriteHelpText);
//
//		$eiGuiField = null;
//		if ($this->guiFieldCallbackOrAssembler instanceof EiGuiField) {
//			$eiGuiField = $this->guiFieldCallbackOrAssembler;
//		} else if ($this->guiFieldCallbackOrAssembler instanceof \Closure) {
//			$eiGuiField = $this->createAssemblerFromClosure($this->guiFieldCallbackOrAssembler);
//		}
//
//		return new SimpleEiGuiProp($eiGuiField, $displayDefinition, []);
//	}

	/**
	 * @return EiGuiProp
	 */
	function toEiGuiProp(): EiGuiProp {

		$displayDefinition = new DisplayDefinition($this->siStructureType, $this->defaultDisplayed,
				$this->overwriteLabel ?? $this->label, $this->overwriteHelpText ?? $this->helpText);

		return new EiGuiPropProxy($this->guiFieldCallback, $displayDefinition);
	}
}