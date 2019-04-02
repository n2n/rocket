<?php
namespace rocket\ei\manage\gui;

interface EiGuiAnglFactory {

	/**
	 * @return CompactContent
	 */
	public function createCompactContent(): CompactContent;	
	
	/**
	 * @return BulkyContent
	 */
	public function createBulkyContent(): BulkyContent;
}
