<?php

namespace rocket\impl\ei\component\prop\meta;

use rocket\op\ei\component\prop\EiPropNature;
use rocket\si\content\impl\meta\SiCrumbGroup;
use n2n\util\type\ArgUtils;

interface AddonEiPropNature extends EiPropNature  {
	/**
	 * @return SiCrumbGroup[]
	 */
	function getPrefixSiCrumbGroups(): array;

	/**
	 * @param SiCrumbGroup[] $prefixSiCrumGroups
	 * @return $this
	 */
	function setPrefixSiCrumbGroups(array $prefixSiCrumGroups): static;

	/**
	 * @return SiCrumbGroup[]
	 */
	function getSuffixSiCrumbGroups(): array;

	/**
	 * @param SiCrumbGroup[] $suffixSiCrumGroups
	 * @return $this
	 */
	function setSuffixSiCrumbGroups(array $suffixSiCrumGroups): static;
}