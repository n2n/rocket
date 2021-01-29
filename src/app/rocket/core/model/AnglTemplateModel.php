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
use n2n\validation\lang\ValidationMessages;
use n2n\l10n\DynamicTextCollection;
use rocket\si\meta\SiMenuGroup;
use rocket\si\meta\SiMenuItem;
use n2n\web\http\nav\Murl;
use rocket\user\model\LoginContext;
use n2n\web\http\controller\ControllerContext;
use rocket\si\control\SiNavPoint;
use n2n\util\uri\Path;

class AnglTemplateModel implements Lookupable {
	private $n2nContext;
	private $rocket;
	private $loginContext;
	
	private function _init(N2nContext $n2nContext, Rocket $rocket, LoginContext $loginContext) {
		$this->n2nContext = $n2nContext;
		$this->rocket = $rocket;
		$this->loginContext = $loginContext;
	}
	
	function createData(ControllerContext $controllerContext) {
		return [
			'translationMap' => $this->createTranslationMap(),	
			'menuGroups' => $this->createSiMenuGroup($controllerContext),
			'user' => $this->loginContext->getCurrentUser()
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
			'show_errors_txt' => $dtc->t('show_errors_txt'),
			'activate_txt' => $dtc->t('activate_txt'),
			'close_layer_text' => $dtc->t('close_layer_text'),
			'search_placeholder_txt' => $dtc->t('search_placeholder_txt'),
			'common_list_tools_label' => $dtc->t('common_list_tools_label'),
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
			'max_elements_err' => str_replace($nr, '{max}', ValidationMessages::maxElements($nr, '{field}')->t($n2nLocale)),
			'user_title' =>  $dtc->t('user_title'),
			'user_groups_title' =>  $dtc->t('user_groups_title'),
			'tool_title' =>  $dtc->t('tool_title'),
			'tool_backup_create_label' => $dtc->t('tool_backup_create_label'),
			'tool_mail_center_title' => $dtc->t('tool_mail_center_title'),
			'mail_center_mail_message_label' => $dtc->t('tool_mail_center_mail_message_label'),
			'mail_center_mail_from_label' => $dtc->t('tool_mail_center_mail_from_label'),
			'mail_center_mail_to_label' => $dtc->t('tool_mail_center_mail_to_label'),
			'mail_center_mail_reply_to_label' => $dtc->t('tool_mail_center_mail_replyto_label'),
			'mail_center_mail_attachments_label' => $dtc->t('tool_mail_center_attatchments_label'),
			'tools_cache_cleared_title' => $dtc->t('tool_clear_cache_title'),
			'tools_cache_cleared_info' => $dtc->t('tool_cache_cleared_info'),
			'original_image_txt' => $dtc->t('original_image_txt'),
			'generated_images_txt' => $dtc->t('generated_images_txt'),
			'image_dimensions_na_txt' => $dtc->t('image_dimensions_na_txt'),
			'ei_impl_locale_not_active_label' => $dtc->t('ei_impl_locale_not_active_label'),
		];
	}
	
	private function createSiMenuGroup($controllerContext) {
		$accessibleLaunchPadIds = $this->getAccesableLaunchPadIds();
		$contextUrl = Murl::controller('rocket')->toUrl($this->n2nContext, $controllerContext);
		
		$siMenuGroups = [];
		
		foreach ($this->rocket->getLayout()->getMenuGroups() as $menuGroup) {
			$siMenuItems = [];
			
			foreach ($menuGroup->getLaunchPads() as $launchPad) {
				if (($accessibleLaunchPadIds !== null && !in_array($launchPad->getId(), $accessibleLaunchPadIds))
						|| !$launchPad->isAccessible($this->n2nContext)) {
					continue;
				}
				
				$navPoint = SiNavPoint::siref((new Path(['manage', $launchPad->getId()]))->toUrl()->ext($launchPad->determinePathExt($this->n2nContext)));
				
				$navPoint->complete($contextUrl);
				
				$siMenuItems[] = new SiMenuItem($launchPad->getId(), $menuGroup->determineLabel($launchPad), $navPoint);
			}
			
			if (empty($siMenuItems)) continue;
			
// 			$open = (null !== $this->activeLaunchPadId) ? $menuGroup->containsLaunchPadId($this->activeLaunchPadId) : false;
			
			$siMenuGroups[] = new SiMenuGroup($menuGroup->getLabel(), $siMenuItems/*, $open*/);
		}
		
		return $siMenuGroups;
	}
	
	
	private function getAccesableLaunchPadIds() {
		$currentUser = $this->loginContext->getCurrentUser();
		
		if ($currentUser->isAdmin()) {
			return null;
		}
		
		$accessibleLaunchPadIds = array();
		
		foreach ($currentUser->getRocketUserGroups() as $userGroup) {
			if (!$userGroup->isLaunchPadAccessRestricted()) {
				return null;
			}
			
			$accessibleLaunchPadIds = array_merge($accessibleLaunchPadIds, $userGroup->getaccessibleLaunchPadIds());
		}
		
		return $accessibleLaunchPadIds;
	}
}
