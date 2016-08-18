<?php
namespace rocket\script\entity\field\impl\string\wysiwyg;

class WysiwygLinkConfigurationExample extends WysiwygLinkConfigurationAdapter {
	
	public function getTitle() {
		return 'Page';
	}
	
	public function getLinkPaths() {
		return array('itusch-overwiew' => 'http://127.0.0.1/php-hnm/default/rocket/public/admin/manage/itusch-itusch/overview',
				'itusch-edit' => 'http://127.0.0.1/php-hnm/default/rocket/public/admin/manage/itusch-itusch/edit/1');
	}
	
}