<?php

namespace rocket\spec;

interface EiComponentNatureProvider {
	/**
	 * Gets called for every EiSetupPhase once if necessary.
	 *
	 * @param EiTypeSetup $eiTypeSetup
	 * @param EiSetupPhase $eiSetupPhase
	 * @return void
	 */
	public function provide(EiTypeSetup $eiTypeSetup, EiSetupPhase $eiSetupPhase): void;
}