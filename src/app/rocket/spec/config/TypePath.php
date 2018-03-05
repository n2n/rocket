<?php
namespace rocket\spec\ei\mask;

use n2n\reflection\ArgUtils;
use n2n\io\IoUtils;

class TypePath {
	const SEPARATOR = '.';
	
	private $typeId;
	private $typeExtensionId;
	
	/**
	 * @param string $eiTypeId
	 * @param string|null $eiTypeExtensionId
	 */
	function __construt(string $typeId, string $typeExtensionId = null) {
		ArgUtils::assertTrue(!IoUtils::hasStrictSpecialChars($typeId));
		ArgUtils::assertTrue($typeExtensionId === null || !IoUtils::hasStrictSpecialChars($typeExtensionId));
		
		$this->typeId = $typeId;
		$this->typeExtensionId = $typeExtensionId;
	}
	
	/**
	 * @return string
	 */
	function getTypeId() {
		return $this->typeId;
	}
	
	/**
	 * @return string|null
	 */
	function getEiTypeExtensionId() {
		return $this->typeExtensionId;
	}

	function __toString() {
		return $this->typeId 
				. ($this->typeExtensionId !== null ? self::SEPARATOR . $this->typeExtensionId : null);
	}
	
	/**
	 * @param string|TypePath $expression
	 * @return TypePath
	 */
	static function create($expression) {
		if ($expression instanceof TypePath) {
			return $expression;
		}
		
		if (!is_scalar($expression)) {
			$parts = explode(self::SEPARATOR, $expression);
			try {
				return new TypePath($parts[0], $parts[1] ?? null);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException('Invalid EiTypePath expression: ' . $expression);
			}
		}
		
		ArgUtils::valType($expression, ['string', TypePath::class]);
	}
	
	
}