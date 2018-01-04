<?php 
namespace rocket\spec\ei\component\field\impl\translation;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\http\controller\ControllerAdapter;
use rocket\spec\ei\manage\util\model\EiuCtrl;
use n2n\web\http\controller\ParamQuery;
use n2n\web\ui\UiComponent;
use rocket\spec\ei\manage\gui\EiGuiViewFactory;
use rocket\spec\ei\manage\gui\GuiDefinition;
use rocket\spec\ei\manage\gui\GuiIdPath;
use n2n\web\http\BadRequestException;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\ui\Raw;
use n2n\impl\web\ui\view\html\HtmlSnippet;
use n2n\web\dispatch\mag\MagCollection;

class TranslationCopyController extends ControllerAdapter {
	public function doLive(EiuCtrl $eiuCtrl, ParamQuery $guiIdPath, ParamQuery $bulky,
			ParamQuery $fromIdRep, ParamQuery $toIdRep = null) {
		$fromEiuEntry = $eiuCtrl->lookupEntry((string) $idRep)->copy();
		$toEiuEntry = null;
		if ($toIdRep === null) {
			$toEiuEntry = $eiuCtrl->frame()->newEntry(false, $fromEiuEntry);
		} else {
			$toEiuEntry = $eiuCtrl->lookupEntry((string) $toIdRep);
		}
				
		$guiIdPath = null;
		try {
			$guiIdPath = GuiIdPath::createFromExpression((string) $guiIdPath);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
		
		if (!$fromEiuEntry->containsGuiProp($guiIdPath)) {
			throw new BadRequestException('Unknown guiIdPath: ' . $guiIdPath);
		}
		
		$eiPropPath = $fromEiuEntry->guiIdPathToEiPropPath($guiIdPath);
		$fromEiuEntry->copyValuesTo($toEiuEntry, [$eiPropPath]);
		
		$toEiuEntry->newEntryGui($bulky->toBool(), true);
		$toEiuEntry->newCustomEntryGui($bulky->toBool());
		
// 		$fromEiuEntry->copyTo($toEi)
		
		
// 		$eiuCtrl->frame()->containsGuiProp($guiIdPath);
// 		$eiuEntry->newEntryGui()->createView();
		
	}
}

class TransGuiViewFactory implements EiGuiViewFactory {
	private $guiIdPath;
	private $propertyPath;
	private $guiDefinition;
	
	public function __construct(GuiIdPath $guiIdPath, PropertyPath $propertyPath, GuiDefinition $guiDefinition) {
		$this->guiIdPath = $guiIdPath;
		$this->propertyPath = $propertyPath;
		$this->guiDefinition = $guiDefinition;
	}
	
	public function getGuiDefinition(): GuiDefinition {
		return $this->guiDefinition;
	}
	
	public function getGuiIdPaths(): array {
		return array($this->guiIdPath);
	}

	public function createView(array $eiEntryGuis, HtmlView $contextView = null): UiComponent {
		$guiField = $this->guiDefinition->getGuiPropByGuiIdPath($this->guiIdPath)->buildGuiField($eiu);
		$editable = null;
		if ($guiField === null || null !== ($editable = $guiField->getEditable())) {
			return new Raw();	
		}
		
		$magCollection = new MagCollection();
		$mag = $editable->createMag();
		$magCollection->addMag($this->propertyPath->getLast()->getPropertyName(), $mag);
		
		return new HtmlSnippet($mag->createUiField($this->propertyPath));
	}
}