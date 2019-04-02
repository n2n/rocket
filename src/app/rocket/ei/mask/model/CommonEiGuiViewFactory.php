<?php
namespace rocket\ei\mask\model;

use rocket\ei\manage\gui\ui\DisplayStructure;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\EiGuiAnglFactory;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\ViewFactory;
use n2n\util\type\CastUtils;
use rocket\ei\util\Eiu;
use n2n\web\ui\UiComponent;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\ViewMode;
use n2n\util\ex\IllegalStateException;
use rocket\angl\zone\Zone;
use rocket\angl\zone\impl\ListZone;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\gui\GuiPropAssembly;
use rocket\ei\manage\gui\EiEntryGui;

class CommonEiGuiAnglFactory implements EiGuiAnglFactory {
	private $eiGui;
	private $guiDefinition;
	private $displayStructure;
	
	public function __construct(EiGui $eiGui, DisplayStructure $displayStructure = null) {
		$this->eiGui = $eiGui;
		$this->displayStructure = $displayStructure;
	}
	
	public function getDisplayStructure() {
		IllegalStateException::assertTrue($this->displayStructure !== null);
		return $this->displayStructure;
	}
	
	public function setDisplayStructure(DisplayStructure $displayStructure) {
		$this->displayStructure = $displayStructure;
	}
	
	/**
	 * @param GuiPropAssembly $guiPropAssembly
	 * @return FieldDeclaration
	 */
	private function createFieldDeclaration(GuiPropAssembly $guiPropAssembly) {
		$n2nLocale = $this->eiGui->getEiFrame()->getN2nContext()->getN2nLocale();
		
		$guiProp = $guiPropAssembly->getGuiProp();
		$label = $guiProp->getDisplayLabelLstr()->t($n2nLocale);
		$helpText = null;
		if (null !== ($helpTextLstr = $guiProp->getDisplayHelpTextLstr())) {
			$helpText = $helpTextLstr->t($n2nLocale);
		}
		
		return new FieldDeclaration($guiPropAssembly->getGuiFieldPath(),
				$label, $helpText);
	}
	
	/**
	 * @param GuiPropAssembly[]
	 * @return FieldDeclaration[]
	 */
	private function createDefaultFieldDeclarations(array $guiPropAssemblies) {
		$fieldDeclarations = [];
		foreach ($this->eiGui->getGuiPropAssemblies() as $guiPropAssembly) {
			$fieldDeclarations[] = $this->createFieldDeclaration($guiPropAssembly); 
		}
		return $fieldDeclarations;
	}
	
	/**
	 * @param DisplayStructure $displayStructure
	 * @return FieldDeclarationStructure[]
	 */
	private function createFieldDeclarationStructures(DisplayStructure $displayStructure) {
		$fieldDeclarationStructures = [];
		foreach ($displayStructure->getDisplayItems() as $displayItem) {
			$guiPropAssembly = $this->eiGui->getGuiPropAssemblyByGuiFieldPath($displayItem->getGuiFieldPath());
			$fieldDeclaration = $this->createFieldDeclaration($guiPropAssembly); 
			
			$children = [];
			if ($displayItem->hasDisplayStructure()) {
				$children = $this->createFieldDeclarationStructures($displayItem->getDisplayStructure());
			}
			
			$fieldDeclarationStructures[] = new FieldDeclarationStructure(
					$displayItem->getType() ?? $guiPropAssembly->getDisplayDefinition()->getDisplayItemType(),
					$fieldDeclaration, $children);
		}
		return $fieldDeclarationStructures;
	}
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 * @return Entry
	 */
	private function createEntry(EiEntryGui $eiEntryGui) {
		$entry = new Entry();
		
		foreach ($eiEntryGui->getGuiFieldAssemblies() as $guiFieldPathStr => $guiFieldAssembly) {
			$entry->putField($guiFieldPathStr, $field);
		}
		
		return $entry;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiGuiAnglFactory::createCompactContent()
	 */
	public function createCompactContent(array $eiEntryGuis): CompactContent {
		$compactContent = new CompactContent($this->createDefaultFieldDeclarations());
		
		$entries = [];
		foreach ($this->eiGui->getEiEntryGuis() as $eiEntryGui) {
			$entries[] = $this->createEntry($eiEntryGui);			
		}
		$compactContent->setEntries($entries);
		
		return $compactContent;
	}
	
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiGuiAnglFactory::createBulkyContent()
	 */
	public function createBulkyContent(): BulkyContent {
		IllegalStateException::assertTrue($this->displayStructure !== null);
		
		$bulkyContent = new BulkyContent($this->createFieldDeclarationStructures($this->displayStructure));
		
		
	}
	
	
// 	private function determineDisplayStructure($viewMode): DisplayStructure {
// 		$displayStructure = null;
	
// 		if ($viewMode & DisplayConfig::COMPACT_VIEW_MODES) {
// 			if (null !== ($overviewDisplayStructure = $this->displayScheme->getOverviewDisplayStructure())) {
// 				return $overviewDisplayStructure;
// 			}
// 			return $this->createDefaultDisplayStructure($viewMode);
// 		} 
		
// 		switch ($viewMode) {
// 			case DisplayConfig::VIEW_MODE_BULKY_READ:
// 				if (null !== ($detailDisplayStructure = $this->displayScheme->getDetailDisplayStructure())) {
// 					return $detailDisplayStructure;
// 				}
// 				break;
// 			case DisplayConfig::VIEW_MODE_BULKY_EDIT:
// 				if (null !== $editDisplayStructure = $this->displayScheme->getEditDisplayStructure()) {
// 					return $editDisplayStructure;
// 				}
// 				break;
// 			case DisplayConfig::VIEW_MODE_BULKY_ADD:
// 				if (null !== ($addDisplayStructure = $this->displayScheme->getAddDisplayStructure())) {
// 					return $addDisplayStructure;
// 				}
// 				break;
// 		}
	
// 		if (null !== ($bulkyDisplayStructure = $this->displayScheme->getBulkyDisplayStructure())) {
// 			return $bulkyDisplayStructure;
// 		}
	
// 		return $this->createDefaultDisplayStructure($viewMode);
// 	}
	
	private function createDefaultDisplayStructure($viewMode) {
		$displayStructure = new DisplayStructure();
		foreach ($this->eiGui->getGuiDefinition()->filterGuiFieldPaths($viewMode) as $eiPropPath) {
			$displayStructure->addGuiFieldPath($eiPropPath);
		}
		return $displayStructure;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiGuiAnglFactory::createUiComponent()
	 */
	public function createUiComponent(array $eiEntryGuis, ?HtmlView $contextView): UiComponent {
		$viewFactory = $this->eiGui->getEiFrame()->getN2nContext()->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		$displayStructure = $this->displayStructure;
		$viewName = null;
		
		if ($this->eiGui->getViewMode() & ViewMode::bulky()) {
			$viewName = 'rocket\ei\mask\view\bulky.html';
		} else {
			$viewName = 'rocket\ei\mask\view\compact.html';
			$displayStructure = $displayStructure->withoutSubStructures();
		}
		
		$params = array('displayStructure' => $displayStructure, 'eiu' => new Eiu($this->eiGui));
		
		if ($contextView !== null) {
			return $contextView->getImport('\\' . $viewName, $params);
		} 
		
		return $viewFactory->create($viewName, $params);
	}
}