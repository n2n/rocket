<?php
namespace rocket\script\core;

use n2n\http\Request;
use n2n\http\Response;
use n2n\l10n\Locale;
use n2n\util\Attributes;
use n2n\core\N2nContext;

interface Script {
	public function getId();
	public function getLabel();
	public function getModule();
	/**
	 * @param Request $request
	 * @param Response $response
	 * @param ManageState $manageState
	 * @return n2n\http\Controller
	 */
	public function createController();
	/**
	 * @param object $obj
	 * @return boolean
	 */
	public function equals($obj);
	/**
	 * @return boolean 
	 */
	public function hasSecurityOptions();
	/**
	 * @param Locale $locale
	 * @return array
	 */
	public function getPrivilegeOptions(N2nContext $n2nContext);
	/**
	 * @param Attributes $attributes
	 * @param Locale $locale
	 * @param N2nContext $n2nContext
	 * @return \n2n\dispatch\option\OptionCollection 
	 */
	public function createAccessOptionCollection(N2nContext $n2nContext);
	
	public function createRestrictionSelectorItems(N2nContext $n2nContext);
	/**
	 * @return \rocket\script\core\extr\ScriptExtraction 
	 */
	public function toScriptExtraction();
}