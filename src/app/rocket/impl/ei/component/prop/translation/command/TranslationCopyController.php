<?php 
namespace rocket\impl\ei\component\prop\translation\command;

use n2n\web\http\controller\ControllerAdapter;
use rocket\ei\util\EiuCtrl;
use n2n\web\http\controller\ParamQuery;
use rocket\ei\manage\gui\field\GuiPropPath;
use n2n\web\http\BadRequestException;
use n2n\impl\web\ui\view\jhtml\JhtmlResponse;
use n2n\util\ex\UnsupportedOperationException;
use n2n\web\dispatch\map\PropertyPath;
use n2n\l10n\N2nLocale;
use n2n\l10n\IllegalN2nLocaleFormatException;

class TranslationCopyController extends ControllerAdapter {
	
	public function doLive(EiuCtrl $eiuCtrl, ParamQuery $guiPropPaths, ParamQuery $propertyPath, ParamQuery $bulky,
			ParamQuery $n2nLocale, ParamQuery $pid = null) {
		try {
			$guiPropPaths = $this->parseGuiPropPaths($guiPropPaths);
			$propertyPath = PropertyPath::createFromPropertyExpression((string) $propertyPath);
			$n2nLocale = N2nLocale::create((string) $n2nLocale);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (IllegalN2nLocaleFormatException $e) {
			throw new BadRequestException(null, null, $e);
		}
		
		$eiuEntry = null;
		if ($pid !== null) {
			$eiuEntry = $eiuCtrl->lookupEntry((string) $pid);
		} else {
			$eiuEntry = $eiuCtrl->frame()->newEntry(false);
			$eiuEntry->getEntityObj()->setN2nLocale($n2nLocale);
		}
		
		foreach ($guiPropPaths as $guitFieldPath) {
			if ($eiuEntry->mask()->engine()->containsGuiProp($guitFieldPath)) continue;
			
			throw new BadRequestException('Unknown eiPropPath: ' . $guitFieldPath);
		}
		
		$eiuEntryGui = $eiuEntry->newCustomEntryGui(function () {
			throw new UnsupportedOperationException();
		}, $guiPropPaths, $bulky->toBool(), true);
		
		$this->send(JhtmlResponse::view($this->createView('jhtmlTranslation.html',
				array('eiuEntryGui' => $eiuEntryGui, 'propertyPath' => $propertyPath,
						'n2nLocale' => $n2nLocale, 'guiPropPaths' => $guiPropPaths))));
	}
	
	private function parseGuiPropPaths(ParamQuery $param) {
		$eiPropPaths = [];
		foreach ($param->toStringArrayOrReject() as $eiPropPathStr) {
			$eiPropPaths[] = GuiPropPath::create((string) $eiPropPathStr);
		}
		if (empty($eiPropPaths)) {
			throw new \InvalidArgumentException('No HuiIdPaths given.');
		}
		return $eiPropPaths;
	}
	
	public function doLiveCopy(EiuCtrl $eiuCtrl, ParamQuery $guiPropPaths, ParamQuery $propertyPath, ParamQuery $bulky,
			ParamQuery $toN2nLocale, ParamQuery $fromPid, ParamQuery $toPid = null) {
				
		try {
			$guiPropPath = current($this->parseGuiPropPaths($guiPropPaths));
			$propertyPath = PropertyPath::createFromPropertyExpression((string) $propertyPath);
			$toN2nLocale = N2nLocale::create((string) $toN2nLocale);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (IllegalN2nLocaleFormatException $e) {
			throw new BadRequestException(null, null, $e);
		}
		
		$fromEiuEntry = $eiuCtrl->lookupEntry((string) $fromPid);
		
		$toEiuEntry = null;
		if ($toPid !== null) {
			$toEiuEntry = $eiuCtrl->lookupEntry((string) $toPid);
		} else {
			$toEiuEntry = $eiuCtrl->frame()->newEntry(false, $fromEiuEntry);
			$toEiuEntry->getEntityObj()->setN2nLocale($toN2nLocale);
		}
		
		if (!$fromEiuEntry->mask()->engine()->containsGuiProp($guiPropPath)) {
			throw new BadRequestException('Unknown guiPropPath: ' . $guiPropPath);
		}
		
		$eiPropPath = $guiPropPath->getFirstEiPropPath();
		$fromEiuEntry->copyValuesTo($toEiuEntry, [$eiPropPath]);
		
		$eiuEntryGui = $toEiuEntry->newCustomEntryGui(function () {
			throw new UnsupportedOperationException();
		}, array($guiPropPath), $bulky->toBool(), true);
		
		$this->send(JhtmlResponse::view($this->createView('jhtmlTranslation.html', 
				array('eiuEntryGui' => $eiuEntryGui, 'propertyPath' => $propertyPath,
						'n2nLocale' => $toN2nLocale, 'guiPropPaths' => [$guiPropPath]))));
	}
}
