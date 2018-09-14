<?php
namespace rocket\ei\manage\control;

use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\impl\web\ui\view\html\HtmlUtils;

class GroupControl implements Control {
	private $controlButton;
	private $controls = array();
	
	public function __construct(ControlButton $controlButton) {
		$this->controlButton = $controlButton;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\control\Control::isStatic()
	 */
	public function isStatic(): bool {
		return $this->controlButton->isStatic();
	}
	
	public function add(Control ...$controls) {
		array_push($this->controls, ...$controls);
		return $this;
	}
	
	public function createUiComponent(array $attrs = array()): UiComponent {
// 		$id = HtmlUtils::buildUniqueId('rocket-control-group-');
		
		$dropdownElem = new HtmlElement('div', HtmlUtils::mergeAttrs(
				array('class' => 'dropdown' . ($this->controlButton->isStatic() ? ' rocket-static' : '')), $attrs));
		$dropdownElem->appendLn();
		$dropdownElem->appendLn($this->controlButton->toButton(
				array('type' => 'button', 'class' => 'dropdown-toggle', /*'id' => $id,*/ 
						'data-toggle' => 'dropdown', 'aria-haspopup' => 'true',
						'aria-expanded' => 'false'), false));
		
		$menuElem = new HtmlElement('div', array('class' => 'dropdown-menu'/*, 'aria-labelledby' => $id*/), '');
		foreach ($this->controls as $control) {
			$menuElem->appendLn($control->createUiComponent(array('class' => 'dropdown-item')));
		}
		$dropdownElem->appendLn($menuElem);
		
		return $dropdownElem;
	}
}

/*
<div class="dropdown">
<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
Dropdown button
</button>
<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
<a class="dropdown-item" href="#">Action</a>
<a class="dropdown-item" href="#">Another action</a>
<a class="dropdown-item" href="#">Something else here</a>
</div>
</div>




<div class="dropdown">
<button type="button" class="dropdown-toggle btn btn-secondary" id="rocket-control-group-dpz42qc4lrt0l1s49" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-hidden="true" title="ei_impl_add_branch_tooltip"><i class="fa fa-plus"></i> <span>ei_impl_insert_branch_label</span></button>
<div class="dropdown-menu" aria-labelledby="rocket-control-group-dpz42qc4lrt0l1s49">
<a href="/php-oxygen/n2n-rocket-playground/src/public/admin/manage/releg-htusch/add/before/4" class="rocket-jhtml btn btn-success rocket-important" data-jhtml="true" data-jhtml-push-to-history="true" data-jhtml-force-reload="false" dropdown-item="" aria-hidden="true" title="Zweig vor diesem Zweig hinzufügen."><i class="fa fa-angle-up"></i> <span>Davor einfügen</span></a>
<a href="/php-oxygen/n2n-rocket-playground/src/public/admin/manage/releg-htusch/add/after/4" class="rocket-jhtml btn btn-success rocket-important" data-jhtml="true" data-jhtml-push-to-history="true" data-jhtml-force-reload="false" dropdown-item="" aria-hidden="true" title="Zweig nach diesem Zweig hinzufügen."><i class="fa fa-angle-down"></i> <span>Danach hinzufügen</span></a>
<a href="/php-oxygen/n2n-rocket-playground/src/public/admin/manage/releg-htusch/add/child/4" class="rocket-jhtml btn btn-success rocket-important" data-jhtml="true" data-jhtml-push-to-history="true" data-jhtml-force-reload="false" dropdown-item="" aria-hidden="true" title="Unterzweig zu diesem Zweig hinzufügen."><i class="fa fa-angle-right"></i> <span>Unterzweig hinzufügen</span></a>
</div>
</div>

*/