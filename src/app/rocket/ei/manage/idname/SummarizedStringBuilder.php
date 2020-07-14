<?php 
namespace rocket\ei\manage\idname;

use n2n\l10n\N2nLocale;
use rocket\ei\manage\EiObject;
use n2n\core\container\N2nContext;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;
use rocket\ei\manage\DefPropPath;

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
	
	public function replaceFields(array $baseIds, IdNameDefinition $idNameDefinition, EiObject $eiObject = null) {
		$eiu = null;
		if ($eiObject !== null) {
			$eiu = new Eiu($this->n2nContext, $eiObject);
		}
		
		foreach ($idNameDefinition->getIdNameProps() as $id => $idNameProp) {
			if (!$idNameProp->isStringRepresentable()) continue;

			$placeholder = self::createPlaceholder($this->createDefPropPath($baseIds, EiPropPath::create($id)));
			if (false === strpos($this->identityStringPattern, $placeholder)) continue;
			
			$this->placeholders[] = $placeholder;
			if ($eiObject === null) {
				$this->replacements[] = '';
			} else {
				$this->replacements[] = $idNameProp->buildIdentityString($eiu, $this->n2nLocale);
			}
		}
		
		foreach ($idNameDefinition->getIdNamePropForks() as $id => $idNamePropFork) {
			$forkedIdNameDefinition = $idNamePropFork->getForkedIdNameDefinition();
			
			if ($forkedIdNameDefinition === null) continue;
			
			$forkedEiFieldSource = null;
			if ($eiObject !== null) {
				$forkedEiFieldSource = $idNamePropFork->determineForkedEiObject($eiu);
			}
			
			$ids = $baseIds;
			$ids[] = EiPropPath::create($id);
			$this->replaceFields($ids, $forkedIdNameDefinition, $forkedEiFieldSource);
		}
	}
	
	private function createDefPropPath(array $baseIds, $id) {
		$ids = $baseIds;
		$ids[] = $id;
		return new DefPropPath($ids);
	}
	
	public static function createPlaceholder($eiPropPath) {
		return self::KNOWN_STRING_FIELD_OPEN_DELIMITER . DefPropPath::create($eiPropPath)
				. self::KNOWN_STRING_FIELD_CLOSE_DELIMITER;
	}
	
	public function __toString(): string {
		return str_replace($this->placeholders, $this->replacements, $this->identityStringPattern);
	}
}
