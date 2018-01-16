<?php
namespace rocket\impl\ei\component\prop\file\command\model;

use n2n\io\managed\img\ImageDimension;
use n2n\reflection\ArgUtils;

class ThumbRatio {
	private $width;
	private $height;
	
	public function __construct(int $width, int $height) {
		$this->width = $width;
		$this->height = $height;
	}
	
	public function buildLabel() {
		return $this->width . ' / ' . $this->height;
	}
	
	public function __toString() {
		return $this->width . ImageDimension::STR_ATTR_SEPARATOR . $this->height;
	}
	
	/**
	 * @param mixed $expr
	 * @return ThumbRatio
	 */
	public static function create($expr) {
		if ($expr instanceof ImageDimension) {
			return self::fromImageDimension($expr);
		}
		
		$expr = ArgUtils::toString($expr);
		
		$parts = explode(ImageDimension::STR_ATTR_SEPARATOR, (string) $expr);
		ArgUtils::assertTrue(count($parts) === 2);
		
		return new ThumbRatio($parts[0], $parts[1]);
	}
	
	private static function fromImageDimension(ImageDimension $imageDimension) {
		$width = $imageDimension->getWidth();
		$height = $imageDimension->getHeight();
		$ggt = self::gcd($width, $height);
		
		return new ThumbRatio($width / $ggt, $height / $ggt);
	}
	
	private static function gcd($num1, $num2) {
		if ($num2 === 0) return $num1;
		
		return self::gcd($num2, $num1 % $num2);
	}
}