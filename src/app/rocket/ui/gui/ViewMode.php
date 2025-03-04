<?php
namespace rocket\ui\gui;

use rocket\ui\si\meta\SiStyle;

class ViewMode {
	const COMPACT_READ = 1;
	const COMPACT_EDIT = 2;
	const COMPACT_ADD = 4;
	
	const BULKY_READ = 8;
	const BULKY_EDIT = 16;
	const BULKY_ADD = 32;
	
	/**
	 * @return int
	 */
	public static function compact() {
		return self::COMPACT_READ | self::COMPACT_EDIT | self::COMPACT_ADD;
	}
	
	/**
	 * @return int
	 */
	public static function bulky() {
		return self::BULKY_READ | self::BULKY_EDIT | self::BULKY_ADD;
	}
	
	/**
	 * @return int
	 */
	public static function read() {
		return self::COMPACT_READ | self::BULKY_READ;
	}
	
	/**
	 * @return int
	 */
	public static function edit() {
		return self::COMPACT_EDIT | self::BULKY_EDIT;
	}
	
	/**
	 * @return int
	 */
	public static function add() {
		return self::COMPACT_ADD | self::BULKY_ADD;
	}
	
	public static function all() {
		return self::COMPACT_READ | self::COMPACT_EDIT | self::COMPACT_ADD 
				| self::BULKY_READ | self::BULKY_EDIT | self::BULKY_ADD;
	}
	
	public static function none() {
		return 0;
	}
	
	public static function determine(bool $bulky, bool $readOnly, bool $new): int {
		if ($readOnly) {
			return $bulky ? self::BULKY_READ : self::COMPACT_READ;
		} else if ($new) {
			return $bulky ? self::BULKY_ADD : self::COMPACT_ADD;
		} else {
			return $bulky ? self::BULKY_EDIT : self::COMPACT_EDIT;
		}
	}
	
	/**
	 * @return int[]
	 */
	public static function getAll() {
		return array(self::COMPACT_READ, self::COMPACT_EDIT, self::COMPACT_ADD,
				self::BULKY_READ, self::BULKY_EDIT, self::BULKY_ADD);
	}
	
	/**
	 * @param int $viewMode
	 * @return boolean
	 */
	static function isReadOnly(int $viewMode) {
		return (bool) ($viewMode & self::read());
	}
	
	/**
	 * @param int $viewMode
	 * @return boolean
	 */
	static function isBulky(int $viewMode) {
		return (bool) ($viewMode & self::bulky());
	}
	
	/**
	 * @param int $viewMode
	 * @return boolean
	 */
	static function isCompact(int $viewMode) {
		return $viewMode & self::compact();
	}
		
	/**
	 * @param int $viewMode
	 * @return boolean
	 */
	static function isAdd(int $viewMode) {
		return $viewMode & self::add();
	}
	
	/**
	 * @param int $viewMode
	 * @return SiStyle
	 */
	static function createSiStyle(int $viewMode) {
		return new SiStyle(ViewMode::isBulky($viewMode), ViewMode::isReadOnly($viewMode), ViewMode::isAdd($viewMode));
	}


	/**
	 * @param int $viewMode
	 * @return string[]
	 */
	static function names(int $viewMode): array {
		$names = [];
		if ($viewMode & self::COMPACT_READ) {
			$names[] = 'COMPACT_READ';
		}
		if ($viewMode & self::COMPACT_EDIT) {
			$names[] = 'COMPACT_EDIT';
		}
		if ($viewMode & self::COMPACT_ADD) {
			$names[] = 'COMPACT_ADD';
		}
		if ($viewMode & self::BULKY_READ) {
			$names[] = 'BULKY_READ';
		}
		if ($viewMode & self::BULKY_EDIT) {
			$names[] = 'BULKY_EDIT';
		}
		if ($viewMode & self::BULKY_ADD) {
			$names[] = 'BULKY_ADD';
		}
		return $names;
	}

	static function stringify(int $viewMode): string {
		return join(', ', self::names($viewMode));
	}
}