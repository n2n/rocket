<?php 
namespace rocket\impl\ei\component\prop\translation\command;

use n2n\web\http\controller\ControllerAdapter;
use rocket\ei\util\EiuCtrl;
use n2n\web\http\controller\ParamQuery;
use rocket\ei\manage\gui\GuiPropPath;
use n2n\web\http\BadRequestException;
use n2n\impl\web\ui\view\jhtml\JhtmlResponse;
use n2n\util\ex\UnsupportedOperationException;
use n2n\web\dispatch\map\PropertyPath;
use n2n\l10n\N2nLocale;
use n2n\l10n\IllegalN2nLocaleFormatException;

class TranslationCopyController extends ControllerAdapter {
	
	public function doLive(EiuCtrl $eiuCtrl, ParamQuery $eiPropPaths, ParamQuery $propertyPath, ParamQuery $bulky,
			ParamQuery $n2nLocale, ParamQuery $pid = null) {
		try {
			$eiPropPaths = $this->parseGuiPropPaths($eiPropPaths);
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
		
		foreach ($eiPropPaths as $eiPropPath) {
			if ($eiuEntry->getEiuEngine()->containsGuiProp($eiPropPath)) continue;
			
			throw new BadRequestException('Unknown eiPropPath: ' . $eiPropPath);
		}
		
		$eiuEntryGui = $eiuEntry->newCustomEntryGui(function () {
			throw new UnsupportedOperationException();
		}, $eiPropPaths, $bulky->toBool(), true);
		
		$this->send(JhtmlResponse::view($this->createView('jhtmlTranslation.html',
				array('eiuEntryGui' => $eiuEntryGui, 'propertyPath' => $propertyPath,
						'n2nLocale' => $n2nLocale, 'eiPropPaths' => $eiPropPaths))));
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
	
	public function doLiveCopy(EiuCtrl $eiuCtrl, ParamQuery $eiPropPaths, ParamQuery $propertyPath, ParamQuery $bulky,
			ParamQuery $toN2nLocale, ParamQuery $fromPid, ParamQuery $toPid = null) {
				
		try {
			$eiPropPath = current($this->parseGuiPropPaths($eiPropPaths));
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
		
		if (!$fromEiuEntry->getEiuEngine()->containsGuiProp($eiPropPath)) {
			throw new BadRequestException('Unknown eiPropPath: ' . $eiPropPath);
		}
		
		$eiPropPath = $fromEiuEntry->getEiuEngine()->eiPropPathToEiPropPath($eiPropPath);
		$fromEiuEntry->copyValuesTo($toEiuEntry, [$eiPropPath]);
		
		$eiuEntryGui = $toEiuEntry->newCustomEntryGui(function () {
			throw new UnsupportedOperationException();
		}, array($eiPropPath), $bulky->toBool(), true);
		
		$this->send(JhtmlResponse::view($this->createView('jhtmlTranslation.html', 
				array('eiuEntryGui' => $eiuEntryGui, 'propertyPath' => $propertyPath,
						'n2nLocale' => $toN2nLocale, 'eiPropPaths' => [$eiPropPath]))));
	}
}
