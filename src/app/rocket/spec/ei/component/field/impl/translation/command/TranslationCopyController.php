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

class TranslationCopyController extends ControllerAdapter {
	public function doLive(EiuCtrl $eiuCtrl, ParamQuery $guiIdPath, ParamQuery $fromIdRep, 
			ParamQuery $toIdRep = null) {
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
		
		$eiPropPath = $fromEiuEntry->guiIdPathToEiPropdPath($guiIdPath);
		
// 		$fromEiuEntry->copyTo($toEi)
		
		
// 		$eiuCtrl->frame()->containsGuiProp($guiIdPath);
// 		$eiuEntry->newEntryGui()->createView();
		
	}
}

class TransGuiViewFactory implements EiGuiViewFactory {
	
	public function __construct(GuiDefinition $guiDefinition) {
		
	}
	
	public function getGuiDefinition(): GuiDefinition {
		return $this->guiDefinition;
	}

	public function createView(array $eiEntryGuis, HtmlView $contextView = null): UiComponent {
	}

	public function getGuiIdPaths(): array {
		return array($this->guiIdPath);
	}	
}