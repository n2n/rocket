<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\spec\ei\component\field\impl\translation\model;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\dispatch\mag\model\ObjectMagAdapter;
use n2n\web\ui\UiComponent;

class ForkMag extends ObjectMagAdapter {
	private $n2nLocaleDefs;	
	
	public function __construct($propertyName, $label, TranslationForm $translationForm, array $n2nLocaleDefs) {
		parent::__construct($propertyName, $label, $translationForm);
		$this->n2nLocaleDefs = $n2nLocaleDefs;
	}
	
	public function getContainerAttrs(HtmlView $view): array {
		return array('class' => 'rocket-impl-translation-manager',
				'data-rocket-impl-tooltip' => $view->getL10nText('ei_impl_tranlsation_manager_tooltip'), null, null, 
						null, 'rocket');
	}
	
	public function createUiField(PropertyPath $propertyPath, HtmlView $view): UiComponent {
		return $view->getImport('\rocket\spec\ei\component\field\impl\translation\view\forkMag.html', 
				array('propertyPath' => $propertyPath->ext('dispatchables'), 'localeDefs' => $this->n2nLocaleDefs));
	}
}
