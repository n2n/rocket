<?php
namespace rocket\script\entity\field\impl\ci\model;

use rocket\script\entity\preview\PreviewModel;

use n2n\ui\html\HtmlView;

use n2n\persistence\orm\InheritanceType;
use n2n\persistence\orm\EntityAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use n2n\persistence\orm\EntityAdapter;

abstract class ContentItem extends EntityAdapter {
	private static function _annotations(AnnotationSet $as) {
		$as->c(EntityAnnotations::TABLE, array('name' => 'rocket_content_item'));
		$as->c(EntityAnnotations::INHERITANCE, array('strategy' => InheritanceType::JOINED));
		$as->c(EntityAnnotations::PROPERTIES, array('names' => array('id', 'panel', 'orderIndex')));
	}
	
	private $id;
	private $panel;
	private $orderIndex;
	
	public function getId() {
		return $this->id;
	}
	
	public function getPanel() {
		return $this->panel;
	}
	
	public function setPanel($panel) {
		$this->panel = $panel;
	}
	
	public function getOrderIndex() {
		return $this->orderIndex;
	}
	
	public function setOrderIndex($orderIndex) {
		$this->orderIndex = $orderIndex;
	}
	
	public abstract function createUiComponent(HtmlView $view);
	
	public abstract function createEditablePreviewUiComponent(PreviewModel $previewModel, HtmlView $view);
}