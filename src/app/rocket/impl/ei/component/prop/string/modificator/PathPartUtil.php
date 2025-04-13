<?php

namespace rocket\impl\ei\component\prop\string\modificator;

use n2n\persistence\orm\criteria\item\CrIt;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\frame\Boundary;
use n2n\persistence\orm\property\EntityProperty;
use rocket\op\ei\manage\generic\ScalarEiProperty;
use rocket\op\ei\manage\generic\GenericEiProperty;
use rocket\op\ei\EiPropPath;
use n2n\util\ex\ExUtils;

class PathPartUtil {

	private ?ScalarEiProperty $baseScalarEiProperty = null;
	private ?GenericEiProperty $uniquePerGenericEiProperty = null;

	function __construct(private EntityProperty $pathPartEntityProperty, private EntityProperty $idEntityProperty) {
	}

	public function setBaseScalarEiProperty(?ScalarEiProperty $baseScalarEiProperty): void {
		$this->baseScalarEiProperty = $baseScalarEiProperty;
	}

	public function setUniquePerGenericEiProperty(?GenericEiProperty $uniquePerGenericEiProperty): void {
		$this->uniquePerGenericEiProperty = $uniquePerGenericEiProperty;
	}

	function containsPathPart(Eiu $eiu, string $pathPart): bool {
		$criteria = $eiu->frame()->createCriteria('e', Boundary::ALL_TYPES)
				->select(CrIt::c('1'));

		$criteria->where()->match(CrIt::p('e', $this->pathPartEntityProperty), '=', $pathPart);

		if (!$eiu->entry()->isNew()) {
			$criteria->where()->andMatch(CrIt::p('e', $this->idEntityProperty), '!=', $eiu->entry()->getId());
		}

		if (null !== $this->uniquePerGenericEiProperty) {
			$criteria->where()->match($this->uniquePerGenericEiProperty->createCriteriaItem(CrIt::p('e')),
					'=', $this->uniquePerGenericEiProperty->buildEntityValue($eiu->entry()->getEiEntry()));
		}

		return null !== $criteria->limit(1)->toQuery()->fetchSingle();
	}

	function determineBaseName(Eiu $eiu, ?EiPropPath $baseEiPropPath): ?string {
		if ($baseEiPropPath === null) {
			return null;
		}

		return ExUtils::try(fn () => $this->baseScalarEiProperty?->eiFieldValueToScalarValue($eiu->entry()->field($baseEiPropPath)->getValue()));
	}
}