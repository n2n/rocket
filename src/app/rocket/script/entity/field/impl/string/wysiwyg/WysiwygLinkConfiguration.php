<?php
namespace rocket\script\entity\field\impl\string\wysiwyg;

use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\ManageInfo;

interface WysiwygLinkConfiguration {
	
	public function __construct(ScriptSelectionMapping $scriptSelectionMapping = null, ManageInfo $manageInfo = null);
	/**
	 * @return string
	 */
	public function getTitle();
	/**
	 * @return array
	 */
	public function getLinkPaths();
	
	public function isOpenInNewWindow();
}