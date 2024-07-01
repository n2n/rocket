<?php
namespace rocket\op\ei\util\factory;

use rocket\ui\si\meta\SiStructureType;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\gui\DisplayDefinition;
use rocket\op\ei\manage\gui\EiGuiField;
use rocket\ui\gui\field\GuiField;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\gui\EiGuiPropSetup;
use rocket\ui\gui\SimpleEiGuiProp;

class EifGuiPropSetup {
	private $guiFieldCallbackOrAssembler;
	private $siStructureType = SiStructureType::ITEM;
	private $defaultDisplayed = true;
	private $overwriteLabel = null;
	private $overwriteHelpText = null;
	
	/**
	 * @param \Closure|EiGuiField $guiFieldCallbackOrAssembler
	 */
	function __construct($guiFieldCallbackOrAssembler) {
		ArgUtils::valType($guiFieldCallbackOrAssembler, ['Closure', EiGuiField::class]);
		$this->guiFieldCallbackOrAssembler = $guiFieldCallbackOrAssembler;
	}

	/**
	 * @param string $siStructureType
	 * @return \rocket\op\ei\util\factory\EifGuiPropSetup
	 */
	function setSiStructureType(string $siStructureType) {
		ArgUtils::valEnum($siStructureType, SiStructureType::all());
		$this->siStructureType = $siStructureType;
		return $this;
	}
	
	/**
	 * @return string
	 */
	function getSiStructureType() {
		return $this->siStructureType;
	}
	
	/**
	 * @param bool $defaultDisplayed
	 * @return \rocket\op\ei\util\factory\EifGuiPropSetup
	 */
	function setDefaultDisplayed(bool $defaultDisplayed) {
		$this->defaultDisplayed = $defaultDisplayed;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isDefaultDisplayed() {
		return $this->defaultDisplayed;
	}
	
	/**
	 * @param string|null $overwriteLabel
	 * @return \rocket\op\ei\util\factory\EifGuiPropSetup
	 */
	function setOverwriteLabel(?string $overwriteLabel) {
		$this->overwriteLabel = $overwriteLabel;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	function getOverwriteLabel() {
		return $this->overwriteLabel;
	}
	
// 	/**
// 	 * @param \Closure|null
// 	 */
// 	function setGuiFieldFactory(?\Closure $closure) {
// 		$this->closure = $closure;
// 	}
	
// 	/**
// 	 * @return \Closure|null
// 	 */
// 	function getGuiFieldFactory() {
// 		return $this->closure;
// 	}
	
	/**
	 * @return EiGuiPropSetup
	 */
	function toGuiProp() {
		$displayDefinition = new DisplayDefinition($this->siStructureType, $this->defaultDisplayed,
				$this->overwriteLabel, $this->overwriteHelpText);
		
		$eiGuiField = null;
		if ($this->guiFieldCallbackOrAssembler instanceof EiGuiField) {
			$eiGuiField = $this->guiFieldCallbackOrAssembler;
		} else if ($this->guiFieldCallbackOrAssembler instanceof \Closure) {
			$eiGuiField = $this->createAssemblerFromClosure($this->guiFieldCallbackOrAssembler);
		} 
		
		return new SimpleEiGuiProp($eiGuiField, $displayDefinition, []);
	}
	
	/**
	 * @param \Closure $guiFieldClosure
	 * @return EiGuiField
	 */
	private function createAssemblerFromClosure($guiFieldClosure) {
		return new class($guiFieldClosure) implements EiGuiField {
			private $guiFieldClosure;
			
			function __construct($guiFieldClosure) {
				$this->guiFieldClosure = $guiFieldClosure;
			}
			
			function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
				$mmi = new MagicMethodInvoker($eiu->getN2nContext());
				$mmi->setClassParamObject(Eiu::class, $eiu);
				$mmi->setParamValue('readOnly', $readOnly);
				$mmi->setReturnTypeConstraint(TypeConstraints::type(GuiField::class, true));
				return $mmi->invoke(null, $this->guiFieldClosure);
			}
		};
	}
}