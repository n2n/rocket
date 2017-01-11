<?php

namespace rocket\spec\ei\component\modificator\impl\adapter;

use rocket\spec\ei\component\modificator\IndependentEiModificator;
use rocket\spec\ei\component\modificator\impl\adapter\EiModificatorAdapter;
use rocket\spec\ei\component\EiConfigurator;

abstract class IndependentEiFieldAdapter extends EiModificatorAdapter implements IndependentEiModificator {
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\IndependentEiComponent::__construct()
	 */
	public function __construct() {
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\modificator\IndependentEiModificator::createEiConfigurator()
	 */
	public function createEiConfigurator(): EiConfigurator {
	}
}