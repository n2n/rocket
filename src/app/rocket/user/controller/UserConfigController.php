<?php
namespace rocket\user\controller;

use rocket\core\model\Rocket;
use rocket\core\model\RocketState;
use n2n\core\DynamicTextCollection;
use rocket\core\model\Breadcrumb;
use n2n\core\MessageContainer;
use rocket\user\model\UserForm;
use n2n\http\ForbiddenException;
use n2n\http\PageNotFoundException;
use rocket\user\model\LoginContext;
use rocket\user\model\UserDao;
use n2n\http\ControllerAdapter;
use n2n\N2N;
use rocket\user\bo\User;

class UserConfigController extends ControllerAdapter {
	private $userDao;
	private $loginContext;
	private $rocketState;
	
	private function _init(UserDao $userDao, LoginContext $loginContext, RocketState $rocketState) {
		$this->userDao = $userDao;
		$this->loginContext = $loginContext;
		$this->rocketState = $rocketState;
	}
	
	public function index() {
		$this->applyBreadcrumbs();
		
		$this->forward('user\view\userOverview.html', array(
				'users' => $this->userDao->getUsers(), 
				'loggedInUser' => $this->loginContext->getCurrentUser()));
	}
	
	public function doAdd(MessageContainer $messageContainer) {
		$tx = N2N::createTransaction();
		
		$userForm = new UserForm(new User(), $this->userDao->getUserGroups());
		
		if ($this->dispatch($userForm, 'save')) {
			$this->userDao->saveUser($userForm->getUser());
			$messageContainer->addInfoCode('user_added_info',
					array('user' => $userForm->getUser()->getNick()));
			$tx->commit();
			
			$this->redirectToController();
			return;
		}
		
		$tx->commit();
		$this->forward('user\view\userEdit.html', array('userForm' => $userForm));
	}
	
	public function doEdit($userId, MessageContainer $messageContainer) {
		$tx = N2N::createTransaction();
		
		$user = $this->userDao->getUserById($userId);
		if (null === $user) {
			$tx->commit();
			throw new PageNotFoundException();
		}

		$loggedInUser = $this->loginContext->getCurrentUser();
		
		$userForm = new UserForm($user, $loggedInUser->isAdmin() ? $this->userDao->getUserGroups() : null);
				
		if (!$loggedInUser->isSuperAdmin() 
				&& !(!$userForm->isNew() && $userForm->getUser()->equals($loggedInUser))) {
			$tx->commit();
			throw new ForbiddenException();
		}
		
		$userForm->setModifyTypeAllowed($userForm->isNew() || 
				($loggedInUser->isSuperAdmin() && !$userForm->getUser()->equals($loggedInUser)));
		
		$this->applyBreadcrumbs($userForm);
		
		if ($this->dispatch($userForm, 'save')) {
			$messageContainer->addInfoCode('user_edited_info', 
					array('user' => $userForm->getUser()->getNick()));
// 			$this->userDao->saveUser($userForm->getUser());
			$tx->commit();
			
			if ($loggedInUser->isAdmin()) {
				$this->redirectToController();
			} else {
				$this->redirectToController(null, null, null, 'rocket\core\controller\RocketController');
			}
			return;
		}
		
		$tx->commit();
		$this->forward('user\view\userEdit.html', array('userForm' => $userForm));
	}
	
	public function doDelete($userId, MessageContainer $messageContainer) {
		$tx = N2N::createTransaction();
		
		$user = $this->userDao->getUserById($userId);
		if ($user === null) {
			$tx->commit();
			throw new PageNotFoundException();
		}
		
		$loggedInUser = $this->loginContext->getCurrentUser();
		if (!$loggedInUser->isSuperAdmin() || $user->equals($loggedInUser)) {
			$tx->commit();
			throw new ForbiddenException();
		}
		
		$this->userDao->deleteUser($user);
		$tx->commit();
		
		$messageContainer->addInfoCode('user_user_deleted_info', array('user' => $user->getNick()));
		
		$this->redirectToController();
	}
	
	private function applyBreadcrumbs(UserForm $userForm = null) {
		$request = $this->getRequest();
		$dtc = new DynamicTextCollection(Rocket::ROCKET_NAMESPACE, $request->getLocale());
	
		$this->rocketState->addBreadcrumb(new Breadcrumb(
				$request->getControllerContextPath($this->getControllerContext()), 
				$dtc->translate('user_title')));
		
		if ($userForm === null) return;
		
		if ($userForm->isNew()) {
			$this->rocketState->addBreadcrumb(new Breadcrumb(
					$request->getControllerContextPath($this->getControllerContext(), array('add')),
					$dtc->translate('user_add_title')));
		} else {
			$user = $userForm->getUser();
			$this->rocketState->addBreadcrumb(new Breadcrumb(
					$request->getControllerContextPath($this->getControllerContext(), 
							array('edit', $user->getId())),
					$dtc->translate('user_edit_title', array('user' => $user->__toString()))));
		}
	}
}