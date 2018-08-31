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
use rocket\ei\util\sort\form\SortForm;

class EiuSortForm implements Dispatchable, UiComponent {
	private static function _annos(AnnoInit $ai) {
		$ai->p('sortForm', new AnnoDispObject());
	}
	
	/**
	 * @var SortDefinition
	 */
	private $sortDefinition;
	/**
	 * @var EiuFactory
	 */
	private $eiuFactory;
	/**
	 * @var SortForm
	 */
	private $sortForm;
	
	function __construct(SortDefinition $sortDefinition, ?SortSetting $sortSetting, EiuFactory $eiuFactory) {
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
	 * @return EiuSortForm
	 */
	function writeSetting(SortSetting $sortSetting) {
		$this->sortForm = new SortForm($sortSetting, $this->sortDefinition);
		return $this;
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\data\FilterPropSettingGroup
	 */
	function readSetting() {
		return $this->sortForm->buildSortSetting();
	}
	
	function getSortForm() {
		return $this->sortForm;
	}
	
	function setSortForm(SortForm $sortForm) {
		$this->sortForm = $sortForm;
	}
	
	private function _validation() {
	}
	
	/**
	 * @return EiuSortForm
	 */
	function clear() {
		$this->sortForm->clear();
		return $this;
	}
	
	/**
	 * @param PropertyPath|null $propertyPath
	 * @return EiuSortForm
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
					array('eiuSortForm' => $this));
		}
		
		$viewFactory = $this->eiuFactory->getN2nContext(true)->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		return $viewFactory->create('rocket\ei\util\sort\view\eiuSortForm.html', array('eiuSortForm' => $this));
	}
	
	public function build(BuildContext $buildContext): string {
		$view = $this->createView($buildContext->getView());
		if (!$view->isInitialized()) {
			$view->initialize(null, $buildContext);
		}
		return $view->getContents();
	}

}