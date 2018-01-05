<?php 
namespace rocket\spec\ei\component\field\impl\translation\command;

use n2n\web\http\controller\ControllerAdapter;
use rocket\spec\ei\manage\util\model\EiuCtrl;
use n2n\web\http\controller\ParamQuery;
use rocket\spec\ei\manage\gui\GuiIdPath;
use n2n\web\http\BadRequestException;
use n2n\impl\web\ui\view\jhtml\JhtmlResponse;
use n2n\util\ex\UnsupportedOperationException;

class TranslationCopyController extends ControllerAdapter {
	public function doLive(EiuCtrl $eiuCtrl, ParamQuery $guiIdPath, ParamQuery $bulky,
			ParamQuery $fromIdRep, ParamQuery $toIdRep = null) {
		$fromEiuEntry = $eiuCtrl->lookupEntry((string) $idRep)->copy();
		$toEiuEntry = null;
		if ($toIdRep !== null) {
			$toEiuEntry = $eiuCtrl->lookupEntry((string) $toIdRep);
		} else {
			$toEiuEntry = $eiuCtrl->frame()->newEntry(false, $fromEiuEntry);
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
		
		$eiuEntryGui = $toEiuEntry->newCustomEntryGui(function () {
			throw new UnsupportedOperationException();
		}, array($guiIdPath), $bulky->toBool());
		
		$this->send(new JhtmlResponse($this->createView('translationCopy.html', 
				array('eiuEntryGui' => $eiuEntryGui, 'propertyPath' => $propertyPath))));
	}
}
