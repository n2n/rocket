<?php
namespace rocket\script\entity\preview;

use rocket\script\entity\field\PropertyScriptField;
use n2n\dispatch\PropertyPath;
use n2n\ui\html\HtmlView;

interface PreviewableScriptField extends PropertyScriptField {
	
	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath, 
			HtmlView $view,	\Closure $createCustomUiElementCallback = null);
}