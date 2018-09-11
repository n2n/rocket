<?php 
namespace rocket\impl\ei\component\prop\translation\command;

use n2n\web\http\controller\ControllerAdapter;
use rocket\ei\util\EiuCtrl;
use n2n\web\http\controller\ParamQuery;
use rocket\ei\manage\gui\GuiIdPath;
use n2n\web\http\BadRequestException;
use n2n\impl\web\ui\view\jhtml\JhtmlResponse;
use n2n\util\ex\UnsupportedOperationException;
use n2n\web\dispatch\map\PropertyPath;
use n2n\l10n\N2nLocale;
use n2n\l10n\IllegalN2nLocaleFormatException;

class TranslationCopyController extends ControllerAdapter {
	
	public function doLive(EiuCtrl $eiuCtrl, ParamQuery $guiIdPaths, ParamQuery $propertyPath, ParamQuery $bulky,
			ParamQuery $n2nLocale, ParamQuery $pid = null) {
		try {
			$guiIdPaths = $this->parseGuiIdPaths($guiIdPaths);
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
		
		foreach ($guiIdPaths as $guiIdPath) {
			if ($eiuEntry->getEiuEngine()->containsGuiProp($guiIdPath)) continue;
			
			throw new BadRequestException('Unknown guiIdPath: ' . $guiIdPath);
		}
		
		$eiuEntryGui = $eiuEntry->newCustomEntryGui(function () {
			throw new UnsupportedOperationException();
		}, $guiIdPaths, $bulky->toBool(), true);
		
		$this->send(JhtmlResponse::view($this->createView('jhtmlTranslation.html',
				array('eiuEntryGui' => $eiuEntryGui, 'propertyPath' => $propertyPath,
						'n2nLocale' => $n2nLocale, 'guiIdPaths' => $guiIdPaths))));
	}
	
	private function parseGuiIdPaths(ParamQuery $param) {
		$guiIdPaths = [];
		foreach ($param->toStringArrayOrReject() as $guiIdPathStr) {
			$guiIdPaths[] = GuiIdPath::create((string) $guiIdPathStr);
		}
		if (empty($guiIdPaths)) {
			throw new \InvalidArgumentException('No HuiIdPaths given.');
		}
		return $guiIdPaths;
	}
	
	public function doLiveCopy(EiuCtrl $eiuCtrl, ParamQuery $guiIdPaths, ParamQuery $propertyPath, ParamQuery $bulky,
			ParamQuery $toN2nLocale, ParamQuery $fromPid, ParamQuery $toPid = null) {
				
		try {
			$guiIdPath = current($this->parseGuiIdPaths($guiIdPaths));
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
		
		if (!$fromEiuEntry->getEiuEngine()->containsGuiProp($guiIdPath)) {
			throw new BadRequestException('Unknown guiIdPath: ' . $guiIdPath);
		}
		
		$eiPropPath = $fromEiuEntry->getEiuEngine()->guiIdPathToEiPropPath($guiIdPath);
		$fromEiuEntry->copyValuesTo($toEiuEntry, [$eiPropPath]);
		
		$eiuEntryGui = $toEiuEntry->newCustomEntryGui(function () {
			throw new UnsupportedOperationException();
		}, array($guiIdPath), $bulky->toBool(), true);
		
		$this->send(JhtmlResponse::view($this->createView('jhtmlTranslation.html', 
				array('eiuEntryGui' => $eiuEntryGui, 'propertyPath' => $propertyPath,
						'n2nLocale' => $toN2nLocale, 'guiIdPaths' => [$guiIdPath]))));
	}
}
