<?php
namespace rocket\ei\manage\gui;

use rocket\si\content\SiBulkyContent;
use rocket\si\content\SiCompactContent;

interface EiGuiGiFactory {

	/**
	 * @return SiCompactContent
	 */
	public function createSiCompactContent(): SiCompactContent;	
	
	/**
	 * @return SiBulkyContent
	 */
	public function createSiBulkyContent(): SiBulkyContent;
}
