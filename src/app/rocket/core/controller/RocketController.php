<?php
namespace rocket\core\controller;

use rocket\tool\controller\ToolController;
use n2n\http\ForbiddenException;
use rocket\user\model\LoginContext;
use rocket\user\controller\UserConfigController;
use rocket\core\model\RocketState;
use rocket\script\core\ManageState;
use n2n\http\PageNotFoundException;
use rocket\module\controller\ModuleController;
use rocket\script\config\controller\ScriptsConfigController;
use n2n\l10n\Locale;
use n2n\http\Request;
use n2n\N2N;
use n2n\http\ControllerAdapter;
use rocket\core\model\DeleteLoginModel;
use rocket\core\model\Rocket;
use n2n\http\HttpStatusException;
use n2n\persistence\DbhPool;
use rocket\script\entity\EntityScript;
use rocket\user\controller\UserGroupConfigController;
use n2n\http\Response;
use rocket\script\core\UnknownMenuItemException;

class RocketController extends ControllerAdapter {
	private $loginContext;
	
	private function _init(Request $request, LoginContext $loginContext) {
		$request->setLocale(new Locale(N2N::getAppConfig()->locale()->getAdminLocaleId()));
		$this->loginContext = $loginContext;
	}
	
	private function verifyUser() {
		if ($this->loginContext->hasCurrentUser()) return true;
		$tx = N2N::createTransaction();
		if ($this->dispatch($this->loginContext, 'login')) {
			$tx->commit();
			$this->refresh();
			return;
		}
		$tx->commit();
		$this->forward('user\view\login.html', array('loginContext' => $this->loginContext));
	}
	
	public function doLogout() {
		$this->loginContext->logout();
		$this->redirectToController();
	}
	
	public function index() {
		if (!$this->verifyUser()) return;
		$deleteLoginModel = new DeleteLoginModel(); 
		$this->dispatch($deleteLoginModel, 'delete');
		$this->forward('core\view\start.html', array('deleteLoginModel' => $deleteLoginModel));
	}
	
	public function doModules(array $contextCmds, array $cmds) {
		if (!N2N::isDevelopmentModeOn()) throw new ForbiddenException();
		if (!$this->verifyUser()) return;
		
		array_push($contextCmds, array_shift($cmds));
		$configController = new ModuleController($this->getRequest(), $this->getResponse());
		$configController->execute($cmds, $contextCmds, $this->getN2nContext());
	}
	
	public function doScripts(array $contextCmds, array $cmds) {
		if (!N2N::isDevelopmentModeOn()) throw new ForbiddenException();
		if (!$this->verifyUser()) return;
		
		array_push($contextCmds, array_shift($cmds));
		$configController = new ScriptsConfigController($this->getRequest(), $this->getResponse());
		$configController->execute($cmds, $contextCmds, $this->getN2nContext());
	}
	
	public function doUsers(array $contextCmds, array $cmds) {
		if (!$this->verifyUser()) return;
		
		array_push($contextCmds, array_shift($cmds));
		$configController = new UserConfigController($this->getRequest(), $this->getResponse());
		$configController->execute($cmds, $contextCmds, $this->getN2nContext());
	}
	
	public function doUserGroups(array $contextCmds, array $cmds) {
		if (!$this->verifyUser()) return;
		
		if (!$this->loginContext->getCurrentUser()->isAdmin()) {
			throw new ForbiddenException();
		}
		
		array_push($contextCmds, array_shift($cmds));
		$configController = new UserGroupConfigController();
		$configController->execute($cmds, $contextCmds, $this->getN2nContext());
	}
	
	public function doManage($navItemId, array $contextCmds, array $cmds, Rocket $rocket, ManageState $manageState, 
			RocketState $rocketState, Locale $locale, DbhPool $dbhPool) {
		if (!$this->verifyUser()) return;
		
		array_push($contextCmds, array_shift($cmds));
		array_push($contextCmds, array_shift($cmds));
		
		$menuItem = null;
		try {
			$menuItem = $rocket->getScriptManager()->getMenuItemById($navItemId);
		} catch (UnknownMenuItemException $e) {
			throw new PageNotFoundException('navitem not found', 0, $e);
		}
		
		$script = $rocket->getScriptManager()->getScriptById($menuItem->getScriptId());
		
		
		
		if ($script instanceof EntityScript) {
			$mask = null;
			if (null !== ($maskId = $menuItem->getMaskId())) {
				$mask = $script->getMaskById($maskId);
			} else {
				$mask = $script->getOrCreateDefaultMask();
			}
			
			if (!sizeof($cmds) && strlen($pathExt = $mask->getOverviewCommand()->getOverviewPathExt())) {
				$this->redirectToController(array('manage', $navItemId, $pathExt));
				return;
			}
		}
		
		$controller = $script->createController();
		$manageState->setSelectedMenuItem($menuItem);
		
		$tx = N2N::createTransaction();
		
		// @todo remove this instanceof hack
		if ($script instanceof EntityScript) {
			$scriptState = $manageState->createScriptState($script, $controller->getControllerContext());
			$scriptState->setScriptMask($mask);
			$em = $script->lookupEntityManager($dbhPool);
			$manageState->setEntityManager($em);
// 			$scriptState->setDraftManager($rocket->getOrCreateDraftManager($em));
			$scriptState->setTranslationManager($rocket->getOrCreateTranslationManager($em));
			$rocket->listen($em);
			$script->setupScriptState($scriptState);
		}
		
		try {
			$controller->execute($cmds, $contextCmds, $this->getN2nContext());
			$tx->commit();
		} catch (HttpStatusException $e) {
			$tx->commit();

			throw $e;
		}
	}
	
	public function doTools(array $contextCmds, array $cmds) {
		if (!$this->verifyUser()) return;

		if (!$this->loginContext->getCurrentUser()->isAdmin()) {
			throw new ForbiddenException();
		}
		
		array_push($contextCmds, array_shift($cmds));
		$toolController = new ToolController($this->getRequest(), $this->getResponse());
		$toolController->execute($cmds, $contextCmds, $this->getN2nContext());
	}
	
	public function notFound() {
		if (!$this->verifyUser()) return;
		
		$this->getResponse()->setStatus(Response::STATUS_404_NOT_FOUND);
		$this->forward('core\view\notFound.html');
	}
	
	public function doAbout() {
		if (!$this->verifyUser()) return;
		
		$this->forward('core\view\about.html');
	}
}