<?php
namespace rocket\core\model;

use n2n\model\RequestScoped;
use n2n\core\DynamicTextCollection;

class ServerInfoExtractor implements RequestScoped {
	
	private $dtc;
	
	private function _init(DynamicTextCollection $dtc) {
		$this->dtc = $dtc;
	}
	
	public function getServerProperties(){
		$server = array();
		$textOn = $this->dtc->translate('common_on_label');
		$textOff = $this->dtc->translate('common_off_label');
		$server['magic_quotes_gpc'] = array (
				'name' =>'Magic Quotes GPC',
				'value' => (ini_get('magic_quotes_gpc') ? $textOn : $textOff),
				'status' => (ini_get('magic_quotes_gpc') == 1 ? false : true)
		);
		$server['register_globals'] = array (
				'name' => 'Register Globals',
				'value' => (ini_get('register_globals') ? $textOn : $textOff),
				'status' => (ini_get('register_globals') == 1 ? false : true)
		);
		$server['max_execution_time'] = array (
				'name' => 'Max. execution time',
				//get the local, not the global values
				//'value' => get_cfg_var('max_execution_time') . ' sec.',
				'value' => ini_get('max_execution_time') . ' sec.',
				'status' => (ini_get('max_execution_time') >= 30 ? true : false)
		);
		$server['upload_max_filesize'] = array (
				'name' => 'Max. upload file size',
				//get the local, not the global values
				//'value' => get_cfg_var('upload_max_filesize'),
				'value' => ini_get('upload_max_filesize'),
				'status' => true
		);
		$server['post_max_size'] = array(
				'name' => 'Post Max Size',
				'value' => ini_get('post_max_size'),
				'status' => true	
		);
		$server['max_input_vars'] = array(
				'name' => 'Max Input Vars',
				'value' => ini_get('max_input_vars'),
				'status' => true	
		);
		$server['memory_limit'] = array(
				'name' => 'Memory Limit',
				'value' => ini_get('memory_limit'),
				'status' => true	
		);
		$server['php_version'] = array (
				'name' => 'PHP Version',
				'value' => phpversion(),
				'status' => (version_compare(phpversion(), '5.4.0') >= 0 ? true : false)
		);
		$server['intl'] = array (
				'name' => 'PECL Intl',
				'value' => (extension_loaded('intl') ? 'active' : 'inactive'),
				'status' =>  (extension_loaded('intl') ? true : false)
		);
		return $server;
	}
}