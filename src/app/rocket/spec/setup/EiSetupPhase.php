<?php

namespace rocket\spec;

/**
 * Used to indicate which EiComponents (EiProp, EiCommand, EiModificator) should be provided in
 * {@link RocketEiComponentNatureProvider::provide()}.
 */
enum EiSetupPhase {
	/**
	 * Clear matches, usually annotated by an attribute or a type that clearly indicates an affiliation to a certain
	 * EiComponent
	 */
	case PERFECT_MATCHES;
	case GOOD_MATCHES;
	/**
	 * Last phase to add to fill still unassigned properties.
	 */
	case SATISFIABLE_MATCHES;
}
