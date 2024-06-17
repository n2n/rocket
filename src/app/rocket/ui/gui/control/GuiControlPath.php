<?php

namespace rocket\ui\gui\control;

use n2n\util\col\Hashable;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use n2n\util\StringUtils;
use rocket\ui\gui\InvalidArgumentException;

class GuiControlPath implements Hashable {
	const FIELD_NAME_SEPARATOR = '/';

	/**
	 * @var string[]
	 */
	protected array $fieldNames = array();

	/**
	 * @param string[] $fieldNames
	 */
	public function __construct(array $fieldNames) {
		ArgUtils::valArray($fieldNames, 'string');

		foreach ($fieldNames as $fieldName) {
			self::valFieldId($fieldName);
			$this->fieldNames[] = $fieldName;
		}
	}

	static function valFieldId(string $fieldName): void {
		if (!StringUtils::contains(self::FIELD_NAME_SEPARATOR,  $fieldName)) {
			return;
		}

		throw new InvalidArgumentException('GuiPath contains invalid field id:' . $fieldName);
	}

	/**
	 * @return int
	 */
	public function size(): int {
		return count($this->fieldNames);
	}

	public function isEmpty(): bool {
		return empty($this->fieldNames);
	}

	protected function ensureNotEmpty(): void {
		if (!$this->isEmpty()) return;

		throw new IllegalStateException('GuiPath is empty.');
	}

	function ext(string $fieldName): GuiControlPath {
		self::valFieldId($fieldName);
		$guiPath = new GuiControlPath([]);
		$guiPath->fieldNames = [...$this->fieldNames, $fieldName];
		return $guiPath;
	}

	/**
	 * @return string[]
	 */
	public function toArray() {
		return $this->fieldNames;
	}

	public function __toString() {
		return implode(self::FIELD_NAME_SEPARATOR, $this->fieldNames);
	}

	public function hashCode(): string {
		return $this->__toString();
	}

//	/**
//	 * @param mixed $expression
//	 * @return DefPropPath
//	 * @throws \InvalidArgumentException
//	 */
//	public static function create(mixed $expression) {
//		if ($expression instanceof DefPropPath) {
//			return $expression;
//		}
//
//		$parts = null;
//		if (is_array($expression)) {
//			$parts = $expression;
//		} else if ($expression instanceof EiPropNature) {
//			return new DefPropPath([string::from($expression)]);
//		} else if ($expression instanceof string) {
//			return new DefPropPath([$expression]);
//		} else if (is_scalar($expression)) {
//			$parts = explode(self::EI_PROP_PATH_SEPARATOR, (string) $expression);
//		} else if ($expression === null) {
//			$parts = [];
//		} else {
//			throw new \InvalidArgumentException('Passed value type can not be converted to a DefPropPath: '
//					. TypeUtils::getTypeInfo($expression));
//		}
//
//		$defPropPath = new DefPropPath([]);
//		$defPropPath->fieldNames = [];
//		foreach ($parts as $part) {
//			$defPropPath->fieldNames[] = string::create($part);
//		}
//		return $defPropPath;
//	}

}