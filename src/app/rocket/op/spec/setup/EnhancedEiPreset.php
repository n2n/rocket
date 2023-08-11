<?php

namespace rocket\op\spec\setup;

use rocket\attribute\EiPreset;
use n2n\reflection\attribute\Attribute;
use rocket\op\ei\EiPropPath;
use n2n\util\ex\err\ConfigurationError;
use n2n\util\type\TypeUtils;
use Throwable;

class EnhancedEiPreset {

	private readonly EiPreset $eiPreset;

	/**
	 * @var PropNotation[]
	 */
	private array $readPropNotations;
	/**
	 * @var PropNotation[]
	 */
	private array $editPropNotations;
	/**
	 * @var PropNotation[]
	 */
	private array $excludePropNotations;
	/**
	 * @var EiPropPath[]
	 */
	private array $parentPropNotations = [];

	function __construct(private readonly Attribute $eiPresetAttribute) {
		$this->eiPreset = $this->eiPresetAttribute->getInstance();
		$this->readPropNotations = $this->createPropNotations($this->eiPreset->readProps);
		$this->editPropNotations = $this->createPropNotations($this->eiPreset->editProps);
		$this->excludePropNotations = $this->createPropNotations($this->eiPreset->excludeProps);
	}

	function getEiPresetAttribute(): Attribute {
		return $this->eiPresetAttribute;
	}

	function getEiPreset(): EiPreset {
		return $this->eiPreset;
	}

	function getMode(): ?EiPresetMode {
		return $this->eiPreset->mode;
	}

	/**
	 * @param EiPropPath $parentEiPropPath
	 * @return PropNotation[]
	 */
	function getReadPropNotations(EiPropPath $parentEiPropPath): array {
		return $this->filterPropNotations($parentEiPropPath, $this->readPropNotations);
	}

	/**
	 * @param EiPropPath $parentEiPropPath
	 * @return PropNotation[]
	 */
	function getEditPropNotations(EiPropPath $parentEiPropPath): array {
		return $this->filterPropNotations($parentEiPropPath, $this->editPropNotations);
	}

	/**
	 * @param EiPropPath $parentEiPropPath
	 * @return PropNotation[]
	 */
	function getParentPropNotations(EiPropPath $parentEiPropPath): array {
		return $this->filterPropNotations($parentEiPropPath, $this->parentPropNotations);
	}

	function containsReadProp(EiPropPath|string $eiPropPath): bool {
		return isset($this->readPropNotations[(string) $eiPropPath]);
	}

	function containsEditProp(EiPropPath|string $eiPropPath): bool {
		return isset($this->editPropNotations[(string) $eiPropPath]);
	}

	function containsExcludedPropNotation(EiPropPath|string $eiPropPath): bool {
		return isset($this->excludePropNotations[(string) $eiPropPath]);
	}

	function containsParentProp(EiPropPath|string $eiPropPath): bool {
		return isset($this->parentPropNotations[(string) $eiPropPath]);
	}

	function getLabel(EiPropPath|string $eiPropPath): ?string {
		$eiPropPathStr = (string) $eiPropPath;
		return ($this->readPropNotations[$eiPropPathStr] ?? $this->editProps[$eiPropPathStr] ?? null)?->getLabel();
	}

	private function filterPropNotations(EiPropPath $parentEiPropPath, array &$propNotations): array {
		$requiredDirectChildSize = $parentEiPropPath->size() + 1;
		return array_filter($propNotations, fn (PropNotation $n)
				=> $requiredDirectChildSize === $n->getEiPropPath()->size()
						&& $n->getEiPropPath()->startsWith($parentEiPropPath));
	}


	function createEiPresetAttributeError(string $propertyPath, Throwable $previous = null,
			string $message = null): ConfigurationError {
		$attrPropName = $this->eiPreset->containsEditProp($propertyPath) ? 'editProps' : 'readProps';

		return $this->createAttributeError('Could not assign property \'' . $propertyPath
				. '\' annotated in '
				. TypeUtils::prettyPropName(EiPreset::class, $attrPropName)
				. ($message === null ? '' : ' Reason: ' . $message), $previous);
	}

	/**
	 * @param string|null $message
	 * @param Throwable|null $previous
	 * @return ConfigurationError
	 */
	function createAttributeError(?string $message, Throwable $previous = null): ConfigurationError {
		return new ConfigurationError($message, $this->eiPresetAttribute->getFile(),
				$this->eiPresetAttribute->getLine(), previous: $previous);
	}


	private function createPropNotations(array $props): array {
		$propNotations = [];
		foreach ($props as $propertyExpression => $label) {
			try {
				$eiPropPath = EiPropPath::create($propertyExpression);
			} catch (\InvalidArgumentException $e) {
				throw $this->createEiPresetAttributeError($propertyExpression, $e);
			}

			$this->registerParents($eiPropPath);

			$propNotations[$propertyExpression] = new PropNotation($eiPropPath, $label);
		}
		return $propNotations;
	}

	private function registerParents(EiPropPath $eiPropPath): void {
		while (true) {
			$eiPropPath = $eiPropPath->poped();
			$eiPropPathStr = (string) $eiPropPath;
			if ($eiPropPath->isEmpty() || isset($this->parentPropNotations[$eiPropPathStr])) {
				return;
			}

			$this->parentPropNotations[$eiPropPathStr] = new PropNotation($eiPropPath, null);
		}
	}
}

class PropNotation {

	function __construct(private EiPropPath $eiPropPath, private ?string $label) {

	}

	function getEiPropPath(): EiPropPath {
		return $this->eiPropPath;
	}

	function getLabel(): ?string {
		return $this->label;
	}

}

