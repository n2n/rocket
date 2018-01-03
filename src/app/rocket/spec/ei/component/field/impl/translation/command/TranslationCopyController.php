<?php 
namespace rocket\spec\ei\component\field\impl\translation;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\dispatch\map\PropertyPath;
use rocket\spec\ei\manage\util\model\EiuCtrl;
use n2n\web\http\controller\ParamQuery;

class TranslationCopyController extends ControllerAdapter {
	public function doLive(EiuCtrl $eiuCtrl, ParamQuery $idRep, PropertyPath $propertyPath) {
		$eiPropPath;
		$eiGuiPath;
		
		$entry = $eiuCtrl->lookupEntry((string) $idRep);
		
		$eiuEntry = $eiuCtrl->frame()->copyEntry($entry);
		
		
		$eiuEntry->newEntryGui();
		
	}
}