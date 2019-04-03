<?php
namespace rocket\ei\manage\gui;

use rocket\gi\content\GiBulkyContent;
use rocket\gi\content\GiCompactContent;

interface EiGuiGiFactory {

	/**
	 * @return GiCompactContent
	 */
	public function createGiCompactContent(): GiCompactContent;	
	
	/**
	 * @return GiBulkyContent
	 */
	public function createGiBulkyContent(): GiBulkyContent;
}
