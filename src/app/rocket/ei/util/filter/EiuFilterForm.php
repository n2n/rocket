<?php
namespace rocket\ei\util\filter;

use rocket\ei\manage\critmod\filter\FilterDefinition;
use n2n\web\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\annotation\AnnoDispObject;
use rocket\ei\util\filter\form\FilterGroupForm;
use rocket\ei\manage\critmod\filter\data\FilterPropSettingGroup;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\BuildContext;
use n2n\web\ui\ViewFactory;
use n2n\reflection\CastUtils;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\ui\UiComponent;
use rocket\ei\util\model\EiuFactory;

class EiuFilterForm implements Dispatchable, UiComponent {
	private static function _annos(AnnoInit $ai) {
		$ai->p('filterGroupForm', new AnnoDispObject());
	}
	
	private $filterDefinition;
	private $eiuFactory;
	private $filterGroupForm;
	
	function __construct(FilterDefinition $filterDefinition, EiuFactory $eiuFactory) {
		$this->filterDefinition = $filterDefinition;
		$this->eiuFactory = $eiuFactory;
		
		$this->filterGroupForm = new FilterGroupForm(new FilterPropSettingGroup(), $filterDefinition);
	}
	
	/**
	 * @param FilterPropSettingGroup $rootGroup
	 * @return EiuFilterForm
	 */
	function setSettings(FilterPropSettingGroup $rootGroup) {
		$this->filterGroupForm = new FilterGroupForm($rootGroup, $this->filterDefinition);
		return $this;
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\data\FilterPropSettingGroup
	 */
	function getSettings() {
		return $this->filterGroupForm->getFilterPropSettingGroup();
	}
	
	/**
	 * @return EiuFilterForm
	 */
	function clear() {
		$this->filterGroupForm->clear();
		return $this;
	}
	
	/**
	 *
	 * @param PropertyPath|null $propertyPath
	 * @return EiuFilterForm
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
			return $contextView->getImport('\rocket\ei\util\filter\view\eiuFilterForm.html',
					array('eiuFilterForm' => $this));
		}
		
		$viewFactory = $this->eiuFactory->getN2nContext(true)->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		return $viewFactory->create('rocket\ei\util\view\eiuEntryForm.html', array('eiuFilterForm' => $this));
	}
	
	public function build(BuildContext $buildContext): string {
		$view = $this->createView($buildContext->getView());
		if (!$view->isInitialized()) {
			$view->initialize(null, $buildContext);
		}
		return $view->getContents();
	}

}