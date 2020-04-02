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
namespace rocket\core\model;

use n2n\context\Lookupable;
use n2n\core\container\N2nContext;
use n2n\validation\impl\ValidationMessages;
use n2n\l10n\DynamicTextCollection;

class AnglTemplateModel implements Lookupable {
	private $n2nContext;
	
	private function _init(N2nContext $n2nContext) {
		$this->n2nContext = $n2nContext;
	}
	
	function getData() {
		return [
			'translationMap' => $this->createTranslationMap()	
		];
	}
	
	function createTranslationMap() {
		$n2nLocale = $this->n2nContext->getN2nLocale();
		
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		
		$nr = 892340289;
		
		return [
			'languages_txt' => $dtc->t('languages_txt'),
			'visible_label' => $dtc->t('visible_label'),
			'errors_txt' => $dtc->t('errors_txt'),
			'activate_txt' => $dtc->t('activate_txt'),
			'search_placeholder_txt' => $dtc->t('search_placeholder_txt'),
			'common_select_label' => $dtc->t('common_select_label'),
			'common_apply_label' => $dtc->t('common_apply_label'),
			'common_cancel_label' => $dtc->t('common_cancel_label'),
			'common_discard_label' => $dtc->t('common_discard_label'),
			'common_edit_label' => $dtc->t('common_edit_label'),
			'common_delete_label' => $dtc->t('common_delete_label'),
			'common_copy_label' => $dtc->t('common_copy_label'),
			'common_add_label' => $dtc->t('common_add_label'),
			'common_edit_all_label' => $dtc->t('common_edit_all_label'),
			'common_open_all_label' => $dtc->t('common_open_all_label'),
			'mandatory_err' => ValidationMessages::mandatory('{field}')->t($n2nLocale),
			'minlength_err' => ValidationMessages::minlength('{minlength}', '{field}')->t($n2nLocale),
			'maxlength_err' => ValidationMessages::maxlength('{maxlength}', '{field}')->t($n2nLocale),
			'min_elements_err' => str_replace($nr, '{min}', ValidationMessages::minElements($nr, '{field}')->t($n2nLocale)),
			'max_elements_err' => str_replace($nr, '{max}', ValidationMessages::maxElements($nr, '{field}')->t($n2nLocale))
		];
	}
}
