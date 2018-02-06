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

use rocket\core\model\RocketState;
use n2n\l10n\DynamicTextCollection;
use rocket\core\model\Breadcrumb;
use n2n\l10n\MessageContainer;
use rocket\user\model\RocketUserForm;
use n2n\web\http\ForbiddenException;
use n2n\web\http\PageNotFoundException;
use rocket\user\model\LoginContext;
use rocket\user\model\RocketUserDao;
use n2n\web\http\controller\ControllerAdapter;
use rocket\user\bo\RocketUser;

class RocketUserController extends ControllerAdapter {
	private $rocketUserDao;
	private $loginContext;
	private $rocketState;
	private $dtc;
	
	private function _init(RocketUserDao $rocketUserDao, LoginContext $loginContext, RocketState $rocketState,
			DynamicTextCollection $dtc) {
		$this->rocketUserDao = $rocketUserDao;
		$this->loginContext = $loginContext;
		$this->rocketState = $rocketState;
		$this->dtc = $dtc;
	}
	
	private function verifyAdmin() {
		if ($this->loginContext->getCurrentUser()->isAdmin()) return;
		
		throw new ForbiddenException();
	}
	
	public function index() {
		$this->verifyAdmin();
		
		$this->applyBreadcrumbs();
		
		$this->forward('..\view\userList.html', array(
				'users' => $this->rocketUserDao->getUsers(), 
				'loggedInUser' => $this->loginContext->getCurrentUser()));
	}
	
	public function doAdd(MessageContainer $messageContainer) {
		$this->verifyAdmin();
		
		$this->beginTransaction();
		
		$rocketUserForm = new RocketUserForm(new RocketUser(), $this->rocketUserDao->getRocketUserGroups());
		$rocketUserForm->setMaxPower($this->loginContext->getCurrentUser()->getPower());
		
		if ($this->dispatch($rocketUserForm, 'save')) {
			$this->rocketUserDao->saveUser($rocketUserForm->getRocketUser());
			$messageContainer->addInfoCode('user_added_info', array('user' => $rocketUserForm->getRocketUser()->getNick()));
			$this->commit();
			
			$this->redirectToController();
			return;
		}
		
		$this->commit();
		$this->forward('..\view\userEdit.html', array('userForm' => $rocketUserForm));
	}
	
	public function doEdit($userId, MessageContainer $messageContainer) {
		$this->verifyAdmin();
		
		$this->beginTransaction();
		
		$user = $this->rocketUserDao->getUserById($userId);
		if (null === $user) {
			throw new PageNotFoundException();
		}

		$currentUser = $this->loginContext->getCurrentUser();
		
		$userForm = new RocketUserForm($user, $this->rocketUserDao->getRocketUserGroups());
		if (!$user->equals($currentUser) && $currentUser->isAdmin()) {
			$userForm->setMaxPower($currentUser->getPower());
		}
		
		$this->applyBreadcrumbs($userForm);
		
		if ($this->dispatch($userForm, 'save')) {
			$messageContainer->addInfoCode('user_edited_info', 
					array('user' => $userForm->getRocketUser()->getNick()));
			$this->commit();
			
			$this->redirectToController();
			return;
		}
		
		$this->commit();
		$this->forward('..\view\userEdit.html', array('userForm' => $userForm));
	}
	


	public function doProfile(MessageContainer $mc) {
		$this->beginTransaction();
			
		$userForm = new RocketUserForm($this->loginContext->getCurrentUser(), 
				$this->rocketUserDao->getRocketUserGroups());
		
		if ($this->dispatch($userForm, 'save')) {
// 			$this->userDao->saveUser($userForm->getUser());
			$this->commit();
			
			$mc->addInfoCode('user_profile_saved_info');
			$this->refresh();
			return;
		}
		
		$this->commit();
		$this->forward('..\view\userEdit.html', array('userForm' => $userForm));
	}
	
	public function doDelete($userId, MessageContainer $messageContainer) {
		$this->verifyAdmin();
		
		$this->beginTransaction();
		
		$user = $this->rocketUserDao->getUserById($userId);
		if ($user === null) {
			throw new PageNotFoundException();
		}
		
		$currentUser = $this->loginContext->getCurrentUser();
		if ((!$currentUser->isSuperAdmin()) || $user->equals($currentUser)) {
			throw new ForbiddenException();
		}
		
		$this->rocketUserDao->deleteUser($user);
		$this->commit();
		
		$messageContainer->addInfoCode('user_deleted_info', array('user' => $user->getNick()));
		
		$this->redirectToController();
	}
	
	private function applyBreadcrumbs(RocketUserForm $userForm = null) {
		$httpContext = $this->getHttpContext();
	
		$this->rocketState->addBreadcrumb(new Breadcrumb(
				$httpContext->getControllerContextPath($this->getControllerContext()), 
				$this->dtc->translate('user_title')));
		
		if ($userForm === null) return;
		
		if ($userForm->isNew()) {
			$this->rocketState->addBreadcrumb(new Breadcrumb(
					$httpContext->getControllerContextPath($this->getControllerContext())->ext('add'),
					$this->dtc->translate('user_add_title')));
		} else {
			$user = $userForm->getRocketUser();
			$this->rocketState->addBreadcrumb(new Breadcrumb(
					$httpContext->getControllerContextPath($this->getControllerContext())
							->ext('edit', $user->getId()),
					$this->dtc->translate('user_edit_title', array('user' => $user->__toString()))));
		}
	}
}
