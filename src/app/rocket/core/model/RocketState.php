<?php
namespace rocket\core\model;

use n2n\model\RequestScoped;
use n2n\util\GenericArrayObject;

class RocketState implements RequestScoped {
	private $breadcrumbs = array();
	
	public function __construct() {
		$this->breadcrumbs = new GenericArrayObject(null, 'rocket\core\model\Breadcrumb');
	}
	
	public function getBreadcrumbs() {
		return $this->breadcrumbs;
	}
	
	public function addBreadcrumb(Breadcrumb $breadcrumb) {
		$this->breadcrumbs[] = $breadcrumb;
	}
	
	public function setSecurityManager() {
		
	}
}