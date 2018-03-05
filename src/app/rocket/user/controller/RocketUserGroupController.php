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
namespace rocket\user\controller;

use n2n\web\http\controller\ControllerAdapter;
use n2n\l10n\DynamicTextCollection;
use rocket\core\model\Breadcrumb;
use rocket\user\model\RocketUserGroupListModel;
use rocket\core\model\Rocket;
use rocket\user\model\RocketUserGroupForm;
use rocket\core\model\RocketState;
use rocket\user\model\RocketUserDao;
use n2n\l10n\MessageContainer;
use n2n\web\http\PageNotFoundException;
use rocket\user\bo\RocketUserGroup;
use n2n\core\N2N;
use rocket\user\model\GroupGrantsViewModel;
use rocket\spec\UnknownSpecException;
use rocket\ei\mask\UnknownEiTypeExtensionException;
use rocket\user\bo\EiGrant;
use rocket\user\model\EiGrantForm;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\ei\manage\critmod\filter\impl\controller\GlobalFilterFieldController;
use rocket\ei\EiEngine;

class RocketUserGroupController extends ControllerAdapter {
	private $rocketState;
	private $userDao;
	private $rocket;
	
	private function _init(RocketState $rocketState, RocketUserDao $userDao, Rocket $rocket) {
		$this->rocketState = $rocketState;
		$this->userDao = $userDao;
		$this->rocket = $rocket;
	}
	
	public function index(Rocket $rocket) {
		$this->applyBreadcrumbs();
		
		$this->forward('..\view\groupOverview.html', array(
				'userGroupOverviewModel' => new RocketUserGroupListModel(
						$this->userDao->getRocketUserGroups(), $rocket->getSpecManager())));
	}
	
	public function doAdd(Rocket $rocket, MessageContainer $messageContainer) {
		$this->beginTransaction();
		
		$userGroupForm = new RocketUserGroupForm(new RocketUserGroup(), $rocket->getLayoutManager(), $rocket->getSpecManager(), $this->getN2nContext());
		if ($this->dispatch($userGroupForm, 'save')) {
			$this->userDao->saveRocketUserGroup($userGroupForm->getRocketUserGroup());
			$this->commit();
			
			$messageContainer->addInfoCode('user_group_added_info',
					array('group' => $userGroupForm->getRocketUserGroup()->getName()));
			$this->redirectToController();
			return;
		}
		$this->commit();
		
		$this->applyBreadcrumbs($userGroupForm);
		$this->forward('..\view\groupEdit.html', array('userGroupForm' => $userGroupForm));
	}
	
	public function doEdit($userGroupId, Rocket $rocket, MessageContainer $messageContainer) {
		$this->beginTransaction();
		$userGroup = $this->userDao->getRocketUserGroupById($userGroupId);
		if ($userGroup === null) {
			$this->commit();
			throw new PageNotFoundException();
		}
		
		$userGroupForm = new RocketUserGroupForm($userGroup, $rocket->getLayoutManager(), $rocket->getSpecManager(), 
				$this->getN2nContext());
		if ($this->dispatch($userGroupForm, 'save')) {
			$this->commit();
			$messageContainer->addInfoCode('user_group_edited_info',
					array('group' => $userGroupForm->getRocketUserGroup()->getName()));
			$this->redirectToController();
			return;	
		}
		
		$this->commit();
		
		$this->applyBreadcrumbs($userGroupForm);
		$this->forward('..\view\groupEdit.html', array('userGroupForm' => $userGroupForm));
	}
	
	public function doDelete($userGroupId, MessageContainer $messageContainer) {
		$this->beginTransaction();
		
		if (null !== ($userGroup = $this->userDao->getRocketUserGroupById($userGroupId))) {
			$this->userDao->removeRocketUserGroup($userGroup);

			$messageContainer->addInfoCode('user_group_removed_info',
					array('group' => $userGroup->getName()));
		}
		
		$this->commit();
		$this->redirectToController();
	}
	
	public function doGrants($rocketUserGroupId, Rocket $rocket) {
		$this->beginTransaction();
		
		$userGroup = $this->userDao->getRocketUserGroupById($rocketUserGroupId);
		if ($userGroup === null) {
			$this->commit();
			throw new PageNotFoundException();
		}
		
		$specManager = $rocket->getSpecManager();
		$groupGrantViewModel = new GroupGrantsViewModel($userGroup, $specManager->getEiTypes(), 
				$specManager->getCustomTypes());
		
		$this->commit();
		
		$this->forward('..\view\groupGrants.html', array('groupGrantsViewModel' => $groupGrantViewModel));
	}
	
	public function doFullyEiGrant($userGroupId, $eiTypeId, $eiMaskId = null, Rocket $rocket) {
		$eiType = null;
		try {
			$eiType = $rocket->getSpecManager()->getEiTypeById($eiTypeId);
		} catch (UnknownSpecException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
		
		if ($eiMaskId !== null && !$eiType->getEiTypeExtensionCollection()->containsId($eiMaskId)) {
			throw new PageNotFoundException();
		}
		
		$this->beginTransaction();
		
		$userGroup = $this->userDao->getRocketUserGroupById($userGroupId);
		if ($userGroup === null) {
			$this->commit();
			throw new PageNotFoundException();
		}
		
		$this->redirectToController(array('grants', $userGroupId));
		
		$eiGrants = $userGroup->getEiGrants();
		foreach ($eiGrants as $eiGrant) {
			if ($eiGrant->getEiTypeId() === $eiTypeId
					&& $eiGrant->getEiMaskId() === $eiMaskId) {
				$eiGrant->setFull(true);
				$this->commit();
				return;
			}
		}
		
		$eiGrant = new EiGrant();
		$eiGrant->setEiTypeId($eiTypeId);
		$eiGrant->setEiMaskId($eiMaskId);
		$eiGrant->setFull(true);
		$eiGrant->setRocketUserGroup($userGroup);
		$eiGrants->append($eiGrant);
		$this->commit();
	}
	
	public function doRemoveGrant($userGroupId, $scriptId) {
		$tx = N2N::createTransaction();
	
		$userGroup = $this->userDao->getRocketUserGroupById($userGroupId);
		if ($userGroup === null) {
			$tx->commit();
			throw new PageNotFoundException();
		}
	
		$this->redirectToController(array('grants', $userGroupId));
	
		$userScriptGrants = $userGroup->getUserSpecGrants();
		foreach ($userScriptGrants as $key => $userScriptGrant) {
			if ($userScriptGrant->getScriptId() === $scriptId) {
				$userScriptGrants->offsetUnset($key);
				$this->userDao->removeUserSpecGrant($userScriptGrant);
				break;
			}
		}
	
		$tx->commit();
	}
	
	/**
	 * @param string $eiTypeId
	 * @param string $eiMaskId
	 * @throws PageNotFoundException
	 * @return EiEngine
	 */
	private function lookupEiEngine(string $eiTypeId, string $eiMaskId = null): EiEngine {
		$eiType = null;
		try {
			$eiType = $this->rocket->getSpecManager()->getEiTypeById($eiTypeId);
		} catch (UnknownSpecException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		if ($eiMaskId !== null) {
			try {
				return $eiType->getEiTypeExtensionCollection()->getById($eiMaskId)->getEiEngine();
			} catch (UnknownEiTypeExtensionException $e) {
				throw new PageNotFoundException(null, 0, $e);
			}
		}
		
		return $eiType->getEiMask()->getEiEngine();
	}
	
	/**
	 * @param int $rocketUserGroupId
	 * @param string $eiTypeId
	 * @param string $eiMaskId
	 * @param ScrRegistry $scrRegistry
	 * @throws PageNotFoundException
	 */
	public function doRestrictEiGrant($rocketUserGroupId, string $eiTypeId, string $eiMaskId = null, ScrRegistry $scrRegistry) {
		$eiEngine = $this->lookupEiEngine($eiTypeId, $eiMaskId);

		$this->beginTransaction();
		
		$rocketUserGroup = $this->userDao->getRocketUserGroupById($rocketUserGroupId);
		if ($rocketUserGroup === null) {
			throw new PageNotFoundException();
		}
		
		$eiGrant = null;
		foreach ($rocketUserGroup->getEiGrants() as $assignedEiGrant) {
			if ($assignedEiGrant->getEiTypeId() === $eiTypeId && $assignedEiGrant->getEiMaskId() === $eiMaskId) {
				$eiGrant = $assignedEiGrant;
			}			
		}
		
		if ($eiGrant === null) {
			$eiGrant = new EiGrant();
			$eiGrant->setRocketUserGroup($rocketUserGroup);
			$eiGrant->setEiTypeId($eiTypeId);
			$eiGrant->setEiMaskId($eiMaskId);
		}
		
		$privilegeDefinition = $eiEngine->createPrivilegeDefinition($this->getN2nContext());
		$eiEntryFilterDefinition = $eiEngine->createEiEntryFilterDefinition($this->getN2nContext());
		
		$eiGrantForm = new EiGrantForm($eiGrant, $privilegeDefinition, $eiEntryFilterDefinition);
		
		if ($this->dispatch($eiGrantForm, 'save')) {
			if ($eiGrantForm->isNew()) {
				$rocketUserGroup->getEiGrants()->append($eiGrant);
			}

			$this->commit();
			$this->redirectToController(array('grants', $rocketUserGroupId));
			return;
		}
		
		$this->commit();
		
		
		$filterAjahHook = GlobalFilterFieldController::buildEiEntryFilterAjahHook($scrRegistry, $eiTypeId, $eiMaskId);
		
		$this->forward('..\view\grantEdit.html', array('eiGrantForm' => $eiGrantForm,
				'filterAjahHook' => $filterAjahHook));
	}	
	
	private function applyBreadcrumbs(RocketUserGroupForm $userGroupForm = null) {
		$httpContext = $this->getHttpContext();
		$dtc = new DynamicTextCollection($this->getModuleNamespace(), $this->getN2nContext()->getN2nLocale());
	
		$this->rocketState->addBreadcrumb(new Breadcrumb(
				$httpContext->getControllerContextPath($this->getControllerContext()), 
				$dtc->translate('user_groups_title')));
		
		if ($userGroupForm === null) return;
		
		if ($userGroupForm->isNew()) {
			$this->rocketState->addBreadcrumb(new Breadcrumb(
					$httpContext->getControllerContextPath($this->getControllerContext(), array('add')),
					$dtc->translate('user_add_group_label')));
		} else {
			$userGroup = $userGroupForm->getRocketUserGroup();
			$this->rocketState->addBreadcrumb(new Breadcrumb(
					$httpContext->getControllerContextPath($this->getControllerContext(), 
							array('edit', $userGroup->getId())),
					$dtc->translate('user_edit_group_breadcrumb', array('user_group' => $userGroup->getName()))));
		}
	}
}
