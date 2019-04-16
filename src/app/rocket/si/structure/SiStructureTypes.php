<?php
namespace rocket\si\structure;

class SiStructureTypes {
	const SIMPLE_GROUP = 'simple-group';
	const MAIN_GROUP = 'main-group';
	const AUTONOMIC_GROUP = 'autonomic-group';
	const LIGHT_GROUP = 'light-group';
	const PANEL = 'panel';
	const ITEM = 'item';
	
	
	/**
	 * @return string[]
	 */
	public static function groups() {
		return array(self::SIMPLE_GROUP, self::MAIN_GROUP, self::AUTONOMIC_GROUP,
				self::LIGHT_GROUP);
	}
	
	/**
	 * @return string[]
	 */
	public static function all() {
		return array(self::ITEM, self::SIMPLE_GROUP, self::MAIN_GROUP, self::AUTONOMIC_GROUP,
				self::LIGHT_GROUP, self::PANEL);
	}
}