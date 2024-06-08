<?php
namespace rocket\op\ei\manage;

use n2n\core\container\N2nContext;
use rocket\op\ei\manage\gui\EiGuiDefinition;
use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;

class EiGuiMaskDeclarationEngine {
	
	function __construct(private readonly N2nContext $n2nContext, private readonly EiGuiDefinition $guiDefinition) {
	}

	/**
	 * @param int $viewMode
	 * @param DefPropPath[]|null $defPropPaths
	 * @return string
	 */
	private function createCacheKey(int $viewMode, ?array $defPropPaths): string {
		$defPropPathsHashPart = '';
		if ($defPropPaths !== null) {
			$defPropPathsHashPart = json_encode(array_map(function($defPropPath) { return (string) $defPropPath; }, $defPropPaths));
		}
		
		return $viewMode . ' ' . $defPropPathsHashPart;
	}
	
	private array $eiGuiMaskDeclarations = [];

	/**
	 * @param int $viewMode
	 * @param array|null $defPropPaths
	 * @return EiGuiMaskDeclaration
	 */
	function obtainEiGuiMaskDeclaration(int $viewMode, ?array $defPropPaths): EiGuiMaskDeclaration {
		$key = $this->createCacheKey($viewMode, null, $defPropPaths);
		
		if (isset($this->eiGuiMaskDeclarations[$key])) {
			return $this->eiGuiMaskDeclarations[$key];
		}
		
		return $this->eiGuiMaskDeclarations[$key] = $this->guiDefinition->createEiGuiMaskDeclaration($this->n2nContext,
				$viewMode, $defPropPaths);
	}
}