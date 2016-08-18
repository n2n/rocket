<?php
namespace rocket\user\controller;

use n2n\http\ControllerAdapter;
use n2n\core\DynamicTextCollection;
use rocket\core\model\Breadcrumb;
use rocket\user\model\UserGroupOverviewModel;
use rocket\core\model\Rocket;
use rocket\user\model\UserGroupForm;
use rocket\core\model\RocketState;
use rocket\user\model\UserDao;
use n2n\core\MessageContainer;
use n2n\http\PageNotFoundException;
use rocket\user\bo\UserGroup;
use n2n\N2N;
use rocket\user\model\GroupGrantsViewModel;
use rocket\user\bo\UserScriptGrant;
use rocket\user\model\UserScriptGrantForm;
use rocket\script\core\UnknownScriptException;

class UserGroupConfigController extends ControllerAdapter {
	private $rocketState;
	private $userDao;
	
	private function _init(RocketState $rocketState, UserDao $userDao) {
		$this->rocketState = $rocketState;
		$this->userDao = $userDao;
	}
	
	public function index(Rocket $rocket) {
		$this->applyBreadcrumbs();
		
		$this->forward('user\view\groupOverview.html', array(
				'userGroupOverviewModel' => new UserGroupOverviewModel(
						$this->userDao->getUserGroups(), $rocket->getScriptManager())));
	}
	
	public function doAdd(Rocket $rocket, MessageContainer $messageContainer) {
		$tx = N2N::createTransaction();
		$userGroupForm = new UserGroupForm(new UserGroup(), $rocket->getScriptManager(), $this->getN2nContext());
		if ($this->dispatch($userGroupForm, 'save')) {
			$this->userDao->saveUserGroup($userGroupForm->getUserGroup());
			$tx->commit();
			$messageContainer->addInfoCode('user_group_added_info',
					array('group' => $userGroupForm->getUserGroup()->getName()));
			$this->redirectToController();
			return;
		}
		$tx->commit();
		
		$this->applyBreadcrumbs($userGroupForm);
		$this->forward('user\view\groupEdit.html', array('userGroupForm' => $userGroupForm));
	}
	
	public function doEdit($userGroupId, Rocket $rocket, MessageContainer $messageContainer) {
		$tx = N2N::createTransaction();
		$userGroup = $this->userDao->getUserGroupById($userGroupId);
		if ($userGroup === null) {
			$tx->commit();
			throw new PageNotFoundException();
		}
		
		$userGroupForm = new UserGroupForm($userGroup, $rocket->getScriptManager(), $this->getN2nContext());
		if ($this->dispatch($userGroupForm, 'save')) {
			$tx->commit();
			$messageContainer->addInfoCode('user_group_edited_info',
					array('group' => $userGroupForm->getUserGroup()->getName()));
			$this->redirectToController();
			return;	
		}
		
		$tx->commit();
		
		$this->applyBreadcrumbs($userGroupForm);
		$this->forward('user\view\groupEdit.html', array('userGroupForm' => $userGroupForm));
	}
	
	public function doDelete($userGroupId, MessageContainer $messageContainer) {
		$tx = N2N::createTransaction();
		
		if (null !== ($userGroup = $this->userDao->getUserGroupById($userGroupId))) {
			$this->userDao->removeUserGroup($userGroup);

			$messageContainer->addInfoCode('user_group_removed_info',
					array('group' => $userGroup->getName()));
		}
		
		$tx->commit();
		$this->redirectToController();
	}
	
	public function doGrants($userGroupId, Rocket $rocket) {
		$tx = N2N::createTransaction();
		$userGroup = $this->userDao->getUserGroupById($userGroupId);
		if ($userGroup === null) {
			$tx->commit();
			throw new PageNotFoundException();
		}
		
		$groupGrantViewModel = new GroupGrantsViewModel($userGroup, $rocket->getScriptManager()->getScripts());
		
		$tx->commit();
		
		$this->forward('user\view\groupGrants.html', array('groupGrantsViewModel' => $groupGrantViewModel));
	}
	
	public function doFullyGrant($userGroupId, $scriptId, Rocket $rocket) {
		if (!$rocket->getScriptManager()->containsScriptId($scriptId)) {
			throw new PageNotFoundException();
		}
		
		$tx = N2N::createTransaction();
		
		$userGroup = $this->userDao->getUserGroupById($userGroupId);
		if ($userGroup === null) {
			$tx->commit();
			throw new PageNotFoundException();
		}
		
		$this->redirectToController(array('grants', $userGroupId));
		
		$userScriptGrants = $userGroup->getUserScriptGrants();
		foreach ($userScriptGrants as $userScriptGrant) {
			if ($userScriptGrant->getScriptId() === $scriptId) {
				$userScriptGrant->setFull(true);
				$tx->commit();
				return;
			}
		}
		
		$userScriptGrant = new UserScriptGrant();
		$userScriptGrant->setScriptId($scriptId);
		$userScriptGrant->setFull(true);
		$userScriptGrant->setUserGroup($userGroup);
		$userScriptGrants->append($userScriptGrant);
		$tx->commit();
	}
	
	public function doRemoveGrant($userGroupId, $scriptId) {
		$tx = N2N::createTransaction();
	
		$userGroup = $this->userDao->getUserGroupById($userGroupId);
		if ($userGroup === null) {
			$tx->commit();
			throw new PageNotFoundException();
		}
	
		$this->redirectToController(array('grants', $userGroupId));
	
		$userScriptGrants = $userGroup->getUserScriptGrants();
		foreach ($userScriptGrants as $key => $userScriptGrant) {
			if ($userScriptGrant->getScriptId() === $scriptId) {
				$userScriptGrants->offsetUnset($key);
				$this->userDao->removeUserScriptGrant($userScriptGrant);
				break;
			}
		}
	
		$tx->commit();
	}
	
	public function doRestrictGrant($userGroupId, $scriptId, Rocket $rocket) {
		$script = null;
		try {
			$script = $rocket->getScriptManager()->getScriptById($scriptId);
		} catch (UnknownScriptException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		$tx = N2N::createTransaction();
		
		$userGroup = $this->userDao->getUserGroupById($userGroupId);
		if ($userGroup === null) {
			$tx->commit();
			throw new PageNotFoundException();
		}
		
		$userScriptGrant = null;
		foreach ($userGroup->getUserScriptGrants() as $assigenedGrant) {
			if ($assigenedGrant->getScriptId() === $scriptId) {
				$userScriptGrant = $assigenedGrant;
			}			
		}
		
		$new = false;
		if ($userScriptGrant === null) {
			$userScriptGrant = new UserScriptGrant();
			$userScriptGrant->setUserGroup($userGroup);
			$new = true;
		}
		
		$userScriptGrantForm = new UserScriptGrantForm($script, $userScriptGrant,
				$script->createAccessOptionCollection($this->getN2nContext()), 
				$script->getPrivilegeOptions($this->getN2nContext()),
				$script->createRestrictionSelectorItems($this->getN2nContext()));
		
		if ($this->dispatch($userScriptGrantForm, 'save')) {
			if ($new) {
				$userGroup->getUserScriptGrants()->append($userScriptGrant);
			}

			$tx->commit();
			$this->redirectToController(array('grants', $userGroupId));
			return;
		}
		
		$tx->commit();
	
		$this->forward('user\view\grantEdit.html', array('userScriptGrantForm' => $userScriptGrantForm));
	}
	
	
	
	private function applyBreadcrumbs(UserGroupForm $userGroupForm = null) {
		$request = $this->getRequest();
		$dtc = new DynamicTextCollection($this->getModule(), $request->getLocale());
	
		$this->rocketState->addBreadcrumb(new Breadcrumb(
				$request->getControllerContextPath($this->getControllerContext()), 
				$dtc->translate('user_groups_title')));
		
		if ($userGroupForm === null) return;
		
		if ($userGroupForm->isNew()) {
			$this->rocketState->addBreadcrumb(new Breadcrumb(
					$request->getControllerContextPath($this->getControllerContext(), array('add')),
					$dtc->translate('user_add_group_label')));
		} else {
			$userGroup = $userGroupForm->getUserGroup();
			$this->rocketState->addBreadcrumb(new Breadcrumb(
					$request->getControllerContextPath($this->getControllerContext(), 
							array('edit', $userGroup->getId())),
					$dtc->translate('user_edit_group_breadcrumb', array('user_group' => $userGroup->getName()))));
		}
	}
}