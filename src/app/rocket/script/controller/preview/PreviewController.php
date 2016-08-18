<?php
namespace rocket\script\controller\preview;

use rocket\script\entity\preview\PreviewModel;
use n2n\reflection\ArgumentUtils;
use n2n\http\ControllerAdapter;

abstract class PreviewController extends ControllerAdapter {
	const PREVIEW_TYPE_DEFAULT = 'default';
	
	private $previewModel;
	private $previewType;
	
	public function setPreviewModel(PreviewModel $previewModel) {
		$this->previewModel = $previewModel;
	}
	
	/**
	 * @return \rocket\script\entity\preview\PreviewModel
	 */
	public function getPreviewModel() {
		return $this->previewModel;
	}
	
	public function setPreviewType($previewType) {
		$options = $this->getPreviewTypeOptions();
		if ((is_array($options) && sizeof($options)) || $previewType != self::PREVIEW_TYPE_DEFAULT) {
			ArgumentUtils::validateEnum($previewType, array_keys($options));
		}
		
		$this->previewType = $previewType;
	}
	
	public function getPreviewType() {
		return $this->previewType;
	}
		
	public abstract function getPreviewTypeOptions();
}