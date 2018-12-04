<?php 
namespace rocket\ei\manage\gui;

use n2n\l10n\N2nLocale;
use rocket\ei\manage\EiObject;
use n2n\core\container\N2nContext;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;

class SummarizedStringBuilder {
	const KNOWN_STRING_FIELD_OPEN_DELIMITER = '{';
	const KNOWN_STRING_FIELD_CLOSE_DELIMITER = '}';
	
	private $identityStringPattern;
	private $n2nContext;
	private $n2nLocale;
	
	private $placeholders = array();
	private $replacements = array();
	
	public function __construct(string $identityStringPattern, N2nContext $n2nContext, N2nLocale $n2nLocale) {
		$this->identityStringPattern = $identityStringPattern;
		$this->n2nContext = $n2nContext;
		$this->n2nLocale = $n2nLocale;
	}
	
	public function replaceFields(array $baseIds, GuiDefinition $guiDefinition, EiObject $eiObject = null) {
		$eiu = null;
		if ($eiObject !== null) {
			$eiu = new Eiu($this->n2nContext, $eiObject);
		}
		
		foreach ($guiDefinition->getGuiProps() as $id => $guiProp) {
			if (!$guiProp->isStringRepresentable()) continue;

			$placeholder = self::createPlaceholder($this->createGuiFieldPath($baseIds, EiPropPath::create($id)));
			if (false === strpos($this->identityStringPattern, $placeholder)) continue;
			
			$this->placeholders[] = $placeholder;
			if ($eiObject === null) {
				$this->replacements[] = '';
			} else {
				$this->replacements[] = $guiProp->buildIdentityString($eiu, $this->n2nLocale);
			}
		}
		
		foreach ($guiDefinition->getGuiPropForks() as $id => $guiPropFork) {
			$forkedGuiDefinition = $guiPropFork->getForkedGuiDefinition();
			
			if ($forkedGuiDefinition === null) continue;
			
			$forkedEiFieldSource = null;
			if ($eiObject !== null) {
				$forkedEiFieldSource = $guiPropFork->determineForkedEiObject($eiu);
			}
			
			$ids = $baseIds;
			$ids[] = EiPropPath::create($id);
			$this->replaceFields($ids, $forkedGuiDefinition, $forkedEiFieldSource);
		}
	}
	
	private function createGuiFieldPath(array $baseIds, $id) {
		$ids = $baseIds;
		$ids[] = $id;
		return new GuiFieldPath($ids);
	}
	
	public static function createPlaceholder($eiPropPath) {
		return self::KNOWN_STRING_FIELD_OPEN_DELIMITER . GuiFieldPath::create($eiPropPath)
				. self::KNOWN_STRING_FIELD_CLOSE_DELIMITER;
	}
	
	public function __toString(): string {
		return str_replace($this->placeholders, $this->replacements, $this->identityStringPattern);
	}
}
