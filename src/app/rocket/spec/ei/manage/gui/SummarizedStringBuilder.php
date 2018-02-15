<?php 
namespace rocket\spec\ei\manage\gui;

use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\EiObject;

class SummarizedStringBuilder {
	const KNOWN_STRING_FIELD_OPEN_DELIMITER = '{';
	const KNOWN_STRING_FIELD_CLOSE_DELIMITER = '}';
	
	private $identityStringPattern;
	private $n2nLocale;
	
	private $placeholders = array();
	private $replacements = array();
	
	public function __construct(string $identityStringPattern, N2nLocale $n2nLocale) {
		$this->identityStringPattern = $identityStringPattern;
		$this->n2nLocale = $n2nLocale;
	}
	
	public function replaceFields(array $baseIds, GuiDefinition $guiDefinition, EiObject $eiObject = null) {
		foreach ($guiDefinition->getLevelGuiProps() as $id => $guiProp) {
			if (!$guiProp->isStringRepresentable()) continue;

			$placeholder = self::createPlaceholder($this->createGuiIdPath($baseIds, $id));
			if (false === strpos($this->identityStringPattern, $placeholder)) continue;
			
			$this->placeholders[] = $placeholder;
			if ($eiObject === null) {
				$this->replacements[] = '';
			} else {
				$this->replacements[] = $guiProp->buildIdentityString($eiObject, $this->n2nLocale);
			}
		}
		
		foreach ($guiDefinition->getGuiPropForks() as $id => $guiPropFork) {
			$forkedEiFieldSource = null;
			if ($eiObject !== null) {
				$forkedEiFieldSource = $guiPropFork->determineForkedEiObject($eiObject);
			}
			
			$ids = $baseIds;
			$ids[] = $id;
			$this->replaceFields($ids, $guiPropFork->getForkedGuiDefinition(), $forkedEiFieldSource);
		}
	}
	
	private function createGuiIdPath(array $baseIds, $id) {
		$ids = $baseIds;
		$ids[] = $id;
		return new GuiIdPath($ids);
	}
	
	public static function createPlaceholder($guiIdPath) {
		return self::KNOWN_STRING_FIELD_OPEN_DELIMITER . GuiIdPath::create($guiIdPath)
				. self::KNOWN_STRING_FIELD_CLOSE_DELIMITER;
	}
	
	public function __toString(): string {
		return str_replace($this->placeholders, $this->replacements, $this->identityStringPattern);
	}
}
