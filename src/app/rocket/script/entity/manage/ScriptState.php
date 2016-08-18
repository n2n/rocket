<?php
namespace rocket\script\entity\manage;

use n2n\persistence\orm\EntityManager;
use rocket\script\entity\command\ScriptCommand;
use n2n\core\IllegalStateException;
use rocket\core\model\Breadcrumb;
use n2n\http\Request;
use n2n\l10n\Locale;
use n2n\http\ControllerContext;
use rocket\script\entity\EntityScript;
use n2n\persistence\orm\criteria\CriteriaProperty;
use rocket\script\entity\mask\ScriptMask;
use n2n\persistence\orm\Entity;
use rocket\script\entity\adaptive\translation\TranslationManager;
use rocket\script\entity\FilterModelFactory;
use rocket\script\core\ManageState;
use rocket\script\entity\security\EntityScriptConstraint;

class ScriptState {
	private $contextEntityScript;
	private $manageState;
	private $controllerContext;
	private $scriptMask;
	private $parent;
	private $translationLocale;
	private $executedScriptCommand;
	private $executedPrivilegeExt;
	private $scriptSelection;
	private $previewType;
	private $criteriaConstraints = array();
	private $softCriteriaConstraints = array();
	private $overviewDisabled = false;
	private $overviewBreadcrumbLabel;
	private $overviewPathExt;
	private $detailDisabled = false;
	private $detailBreadcrumbLabel;
	private $detailPathExt;
	private $parentScriptState;
	private $scriptRelations = array();
	private $scriptStateListeners = array();
	private $criteriaFactory = null;
	private $entityManager;
	private $entityScriptConstraint;
	private $commandExecutionConstraint;
	/**
	 * @param EntityScript $contextEntityScript
	 * @param ControllerContext $controllerContext
	 * @param HardFilter $hardFilter
	 */
	public function __construct(EntityScript $contextEntityScript, ManageState $manageState, $pseudo) {
		$this->contextEntityScript = $contextEntityScript;
		$this->manageState = $manageState;
		$this->pseudo = $pseudo;
		$this->entityScriptConstraint = $manageState->getSecurityManager()->getEntityScriptConstraintByEntityScript($contextEntityScript);
	}
	/**
	 * @param ScriptState $parent
	 */
	public function setParent(ScriptState $parent) {
		$this->parent = $parent;
	}
	/**
	 * @return ScriptState
	 */
	public function getParent() {
		return $this->parent;
	}
	/**
	 * @return \rocket\script\entity\EntityScript
	 */
	public function getContextEntityScript() {
		return $this->contextEntityScript;
	}
	
	public function isPseudo() {
		return $this->pseudo;
	}
	/**
	 * @param ControllerContext $controllerContext
	 */
	public function setControllerContext(ControllerContext $controllerContext) {
		$this->controllerContext = $controllerContext;
	}
	/**
	 * @return ControllerContext
	 */
	public function getControllerContext() {
		if (null === $this->controllerContext) {
			throw new IllegalStateException('ScriptState has no ControllerContext available');
		}
		
		return $this->controllerContext;
	}
	
	public function setScriptMask(ScriptMask $scriptMask) {
		$this->scriptMask = $scriptMask;
	}
	
	public function getScriptMask() {
		if ($this->scriptMask === null) {
			$this->scriptMask = $this->contextEntityScript->getOrCreateDefaultMask();
		}
		
		return $this->scriptMask;
	}
	/**
	 * @return Locale
	 */
	public function getLocale() {
		return $this->manageState->getN2nContext()->getLocale();
	}
	
	public function getManageState() {
		return $this->manageState;
	}
	
	public function getEntityScriptConstraint() {
		return $this->entityScriptConstraint;
	}
	
	public function setEntityScriptConstraint(EntityScriptConstraint $entityScriptConstraint = null) {
		$this->entityScriptConstraint = $entityScriptConstraint;
	}
	
	public function setExecutedScriptCommand(ScriptCommand $scriptCommand) {
		if (!$this->isScriptCommandAvailable($scriptCommand)) {
			throw new \InvalidArgumentException('No access to this ScriptCommand.');
		}
		$this->executedScriptCommand = $scriptCommand;
		$this->commandExecutionConstraint = null;
	}
	/**
	 * @throws \n2n\core\IllegalStateException
	 * @return \rocket\script\entity\command\ScriptCommand
	 */
	public function getExecutedScriptCommand() {
		if (null === $this->executedScriptCommand) {
			throw new IllegalStateException('ScriptState has no executed ScriptCommand');
		}
		
		return $this->executedScriptCommand;
	}
	
	public function setExecutedPrivilegeExt($privilegeExt) {
		if ($this->isScriptCommandAvailable($this->getExecutedScriptCommand(), $privilegeExt)) {
			throw new \InvalidArgumentException('No access to passed privilegeExt.'); 
		}
		$this->executedPrivilegeExt = $privilegeExt;
		$this->commandExecutionConstraint = null;
	}
	
	public function getExecutedPrivilegeExt() {
		return $this->executedPrivilegeExt;
	}
	
	public function getCommandExecutionConstraint() {
		if ($this->entityScriptConstraint !== null && $this->commandExecutionConstraint === null) {
			$this->commandExecutionConstraint = $this->entityScriptConstraint->createCommandExecutionConstraint(
					$this->getExecutedScriptCommand(), $this->getExecutedPrivilegeExt());
		}
		
		return $this->commandExecutionConstraint;
	}
	/**
	 * @throws \n2n\core\IllegalStateException
	 * @return \n2n\persistence\orm\EntityManager
	 */
	public function getEntityManager() {
		return $this->manageState->getEntityManager();
	}
	
	public function getN2nContext() {
		return $this->manageState->getN2nContext();
	}
	
	public function hasScriptSelection() {
		return isset($this->scriptSelection);
	}
	
	public function setScriptSelection(ScriptSelection $scriptSelection = null) {
		$this->scriptSelection = $scriptSelection;
	}
	/**
	 * @return ScriptSelection
	 */
	public function getScriptSelection() {
		return $this->scriptSelection;
	}
	
	private function ensureScriptSelectionExists() {
		if (!$this->hasScriptSelection()) {
			throw new IllegalStateException('ScriptState has no ScriptSelection');
		}
	}
	
	public function addCriteriaConstraint(CriteriaConstraint $criteriaConstraint) {
		$this->criteriaConstraints[] = $criteriaConstraint;
	}
	/**
	 * @return CriteriaConstraint[]
	 */
	public function getCriteriaConstraints() {
		return $this->criteriaConstraints;
	}
	
	public function removeCriteriaConstraints() {
		$this->criteriaConstraints = array();
	}
	/**
	 * @param CriteriaFactory $criteriaFactory
	 */
	public function setCriteriaFactory(CriteriaFactory $criteriaFactory) {
		$this->criteriaFactory = $criteriaFactory;
	}
	/**
	 * @param \n2n\persistence\orm\EntityManager $em
	 * @param string $entityAlias
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function createCriteria(EntityManager $em, $entityAlias, $applyDefaultSort = true, 
			$applySecurityConstraints = true, $applyMaskConstraints = true, $applyCustomConstraints = true) {
		$criteria = null;
		
		if (isset($this->criteriaFactory)) {
			$criteria = $this->criteriaFactory->create($em, $entityAlias);
		} else {
			$criteria = $em->createCriteria($this->contextEntityScript->getEntityModel()->getClass(), $entityAlias);
		}
		
		if ($applyCustomConstraints) {
			foreach ($this->criteriaConstraints as $criteriaConstraint) {
				$criteriaConstraint->applyToCriteria($criteria, new CriteriaProperty(array($entityAlias)));
			}
		}
		
		if ($applyMaskConstraints && null !== ($filterData = $this->getScriptMask()->getFilterData())) {
			$this->getOrCreateFilterModel()->createCriteriaConstraint($filterData)
					->applyToCriteria($criteria, new CriteriaProperty(array($entityAlias)));
		}

		if ($applyDefaultSort) {
			$defaultSortDirections = $this->getScriptMask()->getDefaultSortDirections();
			if (!empty($defaultSortDirections) 
					&& null !== ($constraint = $this->getOrCreateSortModel()
							->createCriteriaConstraint($defaultSortDirections))) {
				$constraint->applyToCriteria($criteria, new CriteriaProperty(array($entityAlias)));
			}
		}
		
		if ($applySecurityConstraints && null !== ($commandExecutionConstraint = $this->getCommandExecutionConstraint())) {
			$commandExecutionConstraint->applyToCriteria($criteria, new CriteriaProperty(array($entityAlias)));
		}
		
		return $criteria;
	}
	
	private $filterModel;
	private $sortModel;
	
	public function getOrCreateFilterModel() {
		if ($this->filterModel !== null) {
			return $this->filterModel;
		}

		return $this->filterModel = FilterModelFactory::createFilterModelFromScriptState($this);
	}
	
	public function getOrCreateSortModel() {
		if ($this->sortModel !== null) {
			return $this->sortModel;
		}
	
		return $this->sortModel = FilterModelFactory::createSortModelFromScriptState($this);
	}
// 	/**
// 	 * @param ScriptStateListener $scriptStateListener
// 	 */
// 	public function registerScriptStateListener(ScriptStateListener $scriptStateListener) {
// 		$this->scriptStateListeners[spl_object_hash($scriptStateListener)] = $scriptStateListener;
// 	}
// 	/**
// 	 * @param ScriptStateListener $scriptStateListener
// 	 */
// 	public function unregisterScriptStateListener(ScriptStateListener $scriptStateListener) {
// 		unset($this->scriptStateListeners[spl_object_hash($scriptStateListener)]);
// 	}
// 	/**
// 	 * If a command adds a new object, this method has to be called
// 	 * @param EntityManager $em
// 	 * @param Entity $object
// 	 */
// 	public function triggerOnNewObject(EntityManager $em, Entity $object) {
// 		foreach ($this->scriptStateListeners as $scriptStateListener) {
// 			$scriptStateListener->onNewObject($em, $this, $object);
// 		}
// 	}
// 	/**
// 	 * If a command removes an object, this method has to be called
// 	 * @param EntityManager $em
// 	 * @param ScriptSelection $scriptSelection
// 	 */
// 	public function triggerOnRemoveObject(EntityManager $em, ScriptSelection $scriptSelection) {
// 		foreach ($this->scriptStateListeners as $scriptStateListener) {
// 			$scriptStateListener->onRemoveObject($em, $this, $scriptSelection);
// 		}
// 	}
		
// 	public function getManageState() {
// 		return $this->manageState;
// 	}
	
// 	public function setTranslationLocale(Locale $translationLocale = null) {
// 		$this->translationLocale = $translationLocale;
// 	}
	
	public function getTranslationLocale() {
		if ($this->scriptSelection !== null && $this->scriptSelection->hasTranslation()) {
			return $this->scriptSelection->getTranslationLocale();
		}
		return $this->getLocale();
	}
	
	public function createKnownString(Entity $entity) {
		return $this->getScriptMask()->createKnownString($entity, $this->getLocale());
	}
	
	public function createOverviewBreadcrumb(Request $request) {
		return new Breadcrumb($this->getOverviewPath($request), $this->getOverviewBreadcrumbLabel());
	}
	
	public function setOverviewDisabled($overviewDisabled) {
		$this->overviewDisabled = $overviewDisabled;
	}
	
	public function isOverviewDisabled() {
		return $this->overviewDisabled;
	}
	
	public function setOverviewBreadcrumbLabel($overviewBreadcrumbLabel) {
		$this->overviewBreadcrumbLabel = $overviewBreadcrumbLabel;
	}
	
	public function getOverviewBreadcrumbLabel() {
		if ($this->overviewDisabled) {
			throw new IllegalStateException('Overview is disabled');
		}
		
		if (null !== $this->overviewBreadcrumbLabel) {
			return $this->overviewBreadcrumbLabel; 
		}
		
		return $this->getScriptMask()->getPluralLabel();
	}
	
	public function setOverviewPathExt($overviewPathExt) {
		$this->overviewPathExt = $overviewPathExt;
	}
	
	public function getOverviewPathExt() {
		return $this->overviewPathExt;
	}
	
	public function isOverviewPathAvailable() {
		return $this->overviewPathExt !== null || $this->getScriptMask()->hasOverviewCommand();
	}
	
	public function getOverviewPath(Request $request) {
		if (isset($this->overviewPathExt)) {
			return $request->getContextPath($this->overviewPathExt);
		} 
				
		return $request->getControllerContextPath($this->getControllerContext(),
				$this->getScriptMask()->getOverviewCommand()->getOverviewPathExt($this->scriptSelection));
	}
	
	public function createDetailBreadcrumb(Request $request) {
		if ($this->detailDisabled) {
			throw new IllegalStateException('Detail is disabled');
		}
		
		return new Breadcrumb(
				$this->getDetailPath($request),
				$this->getDetailBreadcrumbLabel());
	}
	
	public function setDetailDisabled($detailDisabled) {
		$this->detailDisabled = $detailDisabled;
	}
	
	public function isDetailDisabled() {
		return $this->detailDisabled;
	}
	
	public function setDetailBreadcrumbLabel($entryDetailBreadcrumbLabel) {
		$this->detailBreadcrumbLabel = $entryDetailBreadcrumbLabel;
	}
		
	public function getDetailBreadcrumbLabel() {		
		if ($this->detailBreadcrumbLabel !== null) {
			return $this->detailBreadcrumbLabel;
		}
	
		$this->ensureScriptSelectionExists();
		$entity = $this->getScriptSelection()->getOriginalEntity();
		
		return $this->contextEntityScript->createKnownString(
				$entity, $this->getLocale());
	}
	
	public function setDetailPathExt($detailPathExt) {
		$this->detailPathExt = $detailPathExt;
	}
	
	public function getDetailPathExt() {
		return $this->detailPathExt;
	}
	
	public function isDetailPathAvailable() {
		return $this->detailPathExt !== null || $this->getScriptMask()->hasEntryDetailCommand();
	}
	
	public function getDetailPath(Request $request, ScriptNavPoint $scriptNavPoint = null) {
		if ($this->detailPathExt !== null) {
			return $request->getContextPath($this->detailPathExt);
		}
		
		if ($scriptNavPoint === null) {
			$this->ensureScriptSelectionExists();
			$scriptNavPoint = $this->scriptSelection->toNavPoint($this->previewType)->copy(true, true);
		}
		
		return $request->getControllerContextPath($this->getControllerContext(),
				$this->getScriptMask()->getEntryDetailCommand()->getEntryDetailPathExt($scriptNavPoint));
	}
	
	public function setPreviewType($previewType) {
		$this->previewType = $previewType;
	}
	
	public function getPreviewType() {
		return $this->previewType;
	}
	/**
	 * @param string $overwriteDraftId
	 * @param Locale $overwriteTranslationLocale
	 * @param string $overwritePreviewType
	 * @return \rocket\script\entity\manage\ScriptNavPoint
	 */
	public function toNavPoint($overwriteDraftId = null, Locale $overwriteTranslationLocale = null, $overwritePreviewType = null) {
		$id = null;
		$draftId = null;
		$translationLocale = null;
		if (isset($this->scriptSelection)) {
			$id = $this->scriptSelection->getId();
			$draftId = $this->scriptSelection->getDraftId();	
			if ($overwriteDraftId) {
				$draftId = $overwriteDraftId;
			}
			$translationLocale = $this->scriptSelection->getTranslationLocale();
			if ($overwriteTranslationLocale) {
				$translationLocale = $overwriteTranslationLocale;
			}
		} 
		
// 		$translationLocale = $this->translationLocale;
// 		if (isset($overwriteTranslationLocale)) {
// 			$translationLocale = $overwriteTranslationLocale;
// 		}
			
		$previewType = $this->previewType;
		if (isset($overwritePreviewType)) {
			$previewType = $overwritePreviewType;
		}
		
		return new ScriptNavPoint($id, $draftId, $translationLocale, $previewType);
	}
	
	public function setScriptRelation($scriptId, ScriptRelation $scriptRelation) {
		$this->scriptRelations[$scriptId] = $scriptRelation;
	}
	
	public function hasScriptRelation($scriptId) {
		return isset($this->scriptRelations[$scriptId]);
	}
	
	public function getScriptRelation($scriptId) {
		if (isset($this->scriptRelations[$scriptId])) {
			return $this->scriptRelations[$scriptId];
		}
		
		return null;
	}
	
	public function isScriptCommandAvailable(ScriptCommand $scriptCommand, $privilegeExt = null) {
		return $this->entityScriptConstraint === null 
				|| $this->entityScriptConstraint->isScriptCommandAvailable($scriptCommand, $privilegeExt);
	}
	
	private $draftManager;
	private $translationManager;

	public function setDraftManager($draftManager) {
		$this->draftManager = $draftManager;
	}
	
	public function getDraftManager() {
		return $this->draftManager;
	}
	
	public function setTranslationManager(TranslationManager $translationManager = null) {
		$this->translationManager = $translationManager;	
	}
	
	public function getTranslationManager() {		
		return $this->translationManager;
	}
	
	public function createChildScriptState(EntityScript $contextEntityScript, ControllerContext $controllerContext, $pseudo) {
		$childScriptState = new ScriptState($contextEntityScript, $this->manageState, $pseudo);
		$childScriptState->setControllerContext($controllerContext);
		$childScriptState->setParent($this);
		$childScriptState->setTranslationManager($this->getTranslationManager());
		$childScriptState->setDraftManager($this->getDraftManager());
// 		$pseudoScriptState->setTranslationLocale($this->getTranslationLocale());
		if ($pseudo) {
			$childScriptState->setOverviewPathExt($this->getOverviewPathExt());
			$childScriptState->setDetailPathExt($this->getDetailPathExt());
		}
		return $childScriptState;
	}
}