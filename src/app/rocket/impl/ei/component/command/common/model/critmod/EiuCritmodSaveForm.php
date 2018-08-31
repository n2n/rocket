<?php
namespace rocket\ei\util\sort;

use n2n\web\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\annotation\AnnoDispObject;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\BuildContext;
use n2n\web\ui\ViewFactory;
use n2n\reflection\CastUtils;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\ui\UiComponent;
use rocket\ei\util\model\EiuFactory;
use rocket\ei\manage\critmod\sort\SortDefinition;
use rocket\ei\manage\critmod\sort\SortSetting;
use rocket\ei\manage\critmod\sort\impl\form\SortForm;
use rocket\ei\manage\critmod\filter\FilterDefinition;

class EiuCritmodSaveForm implements Dispatchable, UiComponent {
	private static function _annos(AnnoInit $ai) {
		$ai->p('critmodSaveForm', new AnnoDispObject());
	}
	
	/**
	 * @var SortDefinition
	 */
	private $quickSearchDefinition;
	/**
	 * @var EiuFactory
	 */
	private $eiuFactory;
	/**
	 * @var SortForm
	 */
	private $critmodSaveForm;
	
	function __construct(FilterDefinition $filterDefinition, FilterJhtmlHook $filterJhtmlHook, SortDefinition $sortDefinition, ?SortSetting $sortSetting, EiuFactory $eiuFactory) {
		$this->sortDefinition = $sortDefinition;
		$this->eiuFactory = $eiuFactory;
		
		$this->writeSetting($rootGroup ?? new SortSetting(), $sortDefinition);
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\sort\SortDefinition
	 */
	function getSortDefinition() {
		return $this->sortDefinition;
	}
	
	/**
	 * @param SortSetting $sortSetting
	 * @return EiuCritmodSaveForm
	 */
	function writeSetting(SortSetting $sortSetting) {
		$this->critmodSaveForm = new SortForm($sortSetting, $this->sortDefinition);
		return $this;
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\data\FilterPropSettingGroup
	 */
	function readSetting() {
		return $this->critmodSaveForm->buildSortSetting();
	}
	
	function getSortForm() {
		return $this->critmodSaveForm;
	}
	
	function setSortForm(SortForm $critmodSaveForm) {
		$this->critmodSaveForm = $critmodSaveForm;
	}
	
	private function _validation() {
	}
	
	/**
	 * @return EiuCritmodSaveForm
	 */
	function clear() {
		$this->critmodSaveForm->clear();
		return $this;
	}
	
	/**
	 * @param PropertyPath|null $propertyPath
	 * @return EiuCritmodSaveForm
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
			return $contextView->getImport('\rocket\ei\util\sort\view\eiuSortForm.html',
					array('eiuFilterForm' => $this));
		}
		
		$viewFactory = $this->eiuFactory->getN2nContext(true)->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		return $viewFactory->create('rocket\ei\util\sort\view\eiuSortForm.html', array('eiuFilterForm' => $this));
	}
	
	public function build(BuildContext $buildContext): string {
		$view = $this->createView($buildContext->getView());
		if (!$view->isInitialized()) {
			$view->initialize(null, $buildContext);
		}
		return $view->getContents();
	}

}