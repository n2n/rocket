<?php
namespace rocket\script\entity\field\impl\string\wysiwyg;

use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\ManageInfo;
abstract class WysiwygLinkConfigurationAdapter implements WysiwygLinkConfiguration {
	
	/**
	 * @var \rocket\script\entity\manage\mapping\ScriptSelectionMapping
	 */
	protected $scriptSelectionMapping;
	/**
	 * @var \rocket\script\entity\field\impl\ManageInfo
	 */
	protected $manageInfo;
	
	public function __construct(ScriptSelectionMapping $scriptSelectionMapping = null, ManageInfo $manageInfo = null){ 
		$this->scriptSelectionMapping = $scriptSelectionMapping;
		$this->manageInfo = $manageInfo;
	}
	
	public function isOpenInNewWindow() {
		return false;
	}	
}