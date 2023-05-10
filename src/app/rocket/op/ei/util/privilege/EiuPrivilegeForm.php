<?php
namespace rocket\op\ei\util\privilege;

use n2n\web\dispatch\Dispatchable;
use rocket\op\ei\manage\security\privilege\PrivilegeDefinition;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\annotation\AnnoDispObject;
use n2n\reflection\annotation\AnnoInit;
use rocket\op\ei\util\EiuAnalyst;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use rocket\op\ei\manage\security\privilege\data\PrivilegeSetting;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\ViewFactory;
use n2n\util\type\CastUtils;
use n2n\web\ui\BuildContext;
use n2n\web\dispatch\map\PropertyPath;
use rocket\op\ei\EiCmdPath;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\impl\web\dispatch\map\val\ValEnum;

class EiuPrivilegeForm implements Dispatchable, UiComponent {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('eiCmdPathStrs'));
		$ai->p('eiPropMagForm', new AnnoDispObject());
	}

	/**
	 * @var PrivilegeDefinition
	 */
	private $privilegeDefinition;
	/**
	 * @var EiuAnalyst
	 */
	private $eiuAnalyst;
	/**
	 * @var PrivilegeSetting
	 */
	private $privilegeSetting;
	/**
	 * @var PropertyPath
	 */
	private $contextPropertyPath;
	
	/**
	 * @var MagForm
	 */
	private $eiPropMagForm;
	
	/**
	 * @param PrivilegeDefinition $privilegeDefinition
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(PrivilegeDefinition $privilegeDefinition, ?PrivilegeSetting $privilegeSetting, EiuAnalyst $eiuAnalyst) {
		$this->privilegeDefinition = $privilegeDefinition;
		$this->eiuAnalyst = $eiuAnalyst;
		
		$this->setSetting($privilegeSetting ?? new PrivilegeSetting());
	}
	
	/**
	 * @return \rocket\op\ei\manage\security\privilege\PrivilegeDefinition
	 */
	function getPrivilegeDefinition() {
		return $this->privilegeDefinition;
	}
	
	/**
	 * @return \rocket\op\ei\manage\security\privilege\data\PrivilegeSetting
	 */
	function getSetting() {
		return $this->privilegeSetting;
	}
	
	/**
	 * @param PrivilegeSetting $privilegeSetting
	 */
	function setSetting(PrivilegeSetting $privilegeSetting) {
		$this->privilegeSetting = $privilegeSetting;
		$this->eiPropMagForm = new MagForm($this->privilegeDefinition->createEiPropPrivilegeMagCollection(
				$privilegeSetting->getEiPropDataSet()));
	}
	
	/**
	 * @return string[]
	 */
	function getEiCmdPathStrs() {
		$eiCmdPathStrs = [];
		foreach ($this->privilegeSetting->getEiCmdPaths() as $key => $eiCmdPath) {
			$eiCmdPathStrs[$key] = (string) $eiCmdPath;
		}
		return array_combine($eiCmdPathStrs, $eiCmdPathStrs);
	}
	
	/**
	 * @param string[] $eiCmdPathStrs
	 */
	function setEiCmdPathStrs(array $eiCmdPathStrs) {
		$eiCmdPaths = array();
		foreach ($eiCmdPathStrs as $eiCmdPathStr) {
			$eiCmdPaths[] = EiCmdPath::create($eiCmdPathStr);
		}
		
		$this->privilegeSetting->setEiCmdPaths($eiCmdPaths);
	}
	
	/**
	 * @return \n2n\impl\web\dispatch\mag\model\MagForm
	 */
	function getEiPropMagForm() {
		return $this->eiPropMagForm;
	}
	
	/**
	 * @param MagForm $magForm
	 */
	function setEiPropMagForm(MagForm $magForm) {
		$this->eiPropMagForm = $magForm;
		
		$this->privilegeSetting->setEiPropDataSet(
				$this->privilegeDefinition->buildEiPropPrivilegeDataSet(
						$magForm->getMagCollection()));
	}
	
	private function buildPrivileges(array &$privileges, array $eiCmdPrivileges, EiCmdPath $baseEiCmdPath)  {
		foreach ($eiCmdPrivileges as $commandPathStr => $eiCmdPrivilege) {
			$commandPath = $baseEiCmdPath->ext($commandPathStr);
			
			$privileges[] = (string) $commandPath;
			
			$this->buildPrivileges($privileges, $eiCmdPrivilege->getSubEiCommandPrivileges(), $commandPath);
		}
	}
	
	private function _validation(BindingDefinition $bd) {
		$commandPathStrs = array();
		$this->buildPrivileges($commandPathStrs, $this->privilegeDefinition->getEiCommandPrivileges(),
				new EiCmdPath(array()));
		$bd->val('eiCmdPathStrs', new ValEnum($commandPathStrs));
	}
	
	/**
	 * @param PropertyPath|null $propertyPath
	 * @return EiuPrivilegeForm
	 */
	public function setContextPropertyPath(?PropertyPath $propertyPath) {
		$this->contextPropertyPath = $propertyPath;
		return $this;
	}
	
	/**
	 * @return \n2n\web\dispatch\map\PropertyPath
	 */
	public function getContextPropertyPath() {
		return $this->contextPropertyPath;
	}
	
	/**
	 * @param HtmlView $contextView
	 * @return \n2n\impl\web\ui\view\html\HtmlView
	 */
	function createView(HtmlView $contextView = null) {
		if ($contextView !== null) {
			return $contextView->getImport('\rocket\op\ei\util\privilege\view\eiuPrivilegeForm.html',
					array('eiuPrivilegeForm' => $this));
		}
		
		$viewFactory = $this->eiuAnalyst->getN2nContext(true)->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		return $viewFactory->create('rocket\op\ei\util\privilege\view\eiuPrivilegeForm.html', 
				array('eiuPrivilegeForm' => $this));
	}
	
	public function build(BuildContext $buildContext): string {
		$view = $this->createView($buildContext->getView());
		if (!$view->isInitialized()) {
			$view->initialize(null, $buildContext);
		}
		return $view->getContents();
	}
	
}