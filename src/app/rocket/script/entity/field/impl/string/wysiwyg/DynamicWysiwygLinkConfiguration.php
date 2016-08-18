<?php
namespace rocket\script\entity\field\impl\string\wysiwyg;

interface DynamicWysiwygLinkConfiguration extends WysiwygLinkConfiguration {
	/**
	 * 
	 */
	public function getLinkBuilderClass();
}