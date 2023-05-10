<?php

namespace rocket\op\util;

use n2n\web\http\HttpContext;
use rocket\op\OpState;
use n2n\web\http\controller\impl\ControllingUtils;
use rocket\op\ei\manage\ManageState;
use rocket\op\ei\util\Eiu;
use n2n\web\http\PageNotFoundException;
use n2n\web\http\ForbiddenException;
use rocket\op\ei\manage\entry\UnknownEiObjectException;
use rocket\op\ei\manage\preview\model\UnavailablePreviewException;
use n2n\web\http\payload\impl\Redirect;
use rocket\op\ei\manage\gui\ViewMode;
use rocket\op\ei\manage\gui\EiGui;
use rocket\op\ei\manage\gui\EiGuiUtil;
use rocket\si\SiPayloadFactory;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\persistence\orm\criteria\Criteria;
use rocket\op\ei\manage\frame\EiFrameUtil;
use rocket\op\ei\manage\LiveEiObject;
use n2n\persistence\orm\util\NestedSetStrategy;
use rocket\si\content\SiGui;
use rocket\op\ei\util\EiuAnalyst;
use n2n\web\ui\UiComponent;
use rocket\si\content\impl\iframe\IframeSiGui;
use rocket\si\content\impl\iframe\IframeData;
use n2n\util\uri\Url;
use rocket\op\ei\manage\gui\EiEntryGui;
use rocket\op\ei\manage\gui\control\GuiControl;
use n2n\web\http\Method;
use rocket\op\ei\manage\api\ZoneApiControlProcess;
use rocket\op\ei\manage\api\ZoneApiControlCallId;
use n2n\web\http\controller\ParamPost;
use rocket\op\ei\manage\api\SiCallResult;
use rocket\si\control\SiNavPoint;
use rocket\si\meta\SiBreadcrumb;
use n2n\l10n\DynamicTextCollection;
use rocket\op\cu\util\Cuu;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\op\ei\util\entry\EiuObject;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\op\cu\gui\CuGui;
use rocket\si\input\SiInputFactory;
use rocket\si\input\SiInputResult;
use rocket\op\cu\gui\control\CuControlCallId;
use rocket\op\cu\util\gui\CufGui;
use n2n\web\http\BadRequestException;

class OpuCtrl {

	private Eiu $eiu;
	private Cuu $cuu;
	private HttpContext $httpContext;
	private OpState $opState;


	/**
	 * Private so future backwards compatible changes can be made.
	 * @param ControllingUtils $cu
	 */
	private function __construct(private ControllingUtils $cu) {
		$manageState = $cu->getN2nContext()->lookup(ManageState::class);
		$this->eiu = new Eiu($manageState->peakEiFrame());
		$this->cuu = new Cuu($cu->getN2nContext(), $this->eiu);
		$this->httpContext = $cu->getHttpContext();
		$this->opState = $cu->getN2nContext()->lookup(OpState::class);
	}

	function eiu(): Eiu {
		return $this->eiu;
	}

	function frame(): EiuFrame {
		return $this->eiu->frame();
	}

	/**
	 * @param string $livePid
	 * @return EiuEntry
	 * @throws PageNotFoundException
	 * @throws ForbiddenException
	 */
	function lookupEntry(string $pid, int $ignoreConstraintTypes = 0): EiuEntry {
		return $this->frame()->entry($this->lookupObject($pid, $ignoreConstraintTypes));
	}

	/**
	 * @param string $livePid
	 * @return \rocket\op\ei\util\entry\EiuObject
	 * @throws PageNotFoundException
	 * @throws ForbiddenException
	 */
	function lookupObject(string $pid, int $ignoreConstraintTypes = 0): EiuObject {
		try {
			return $this->frame()->lookupObject($this->frame()->pidToId($pid), $ignoreConstraintTypes, true);
		} catch (UnknownEiObjectException|\InvalidArgumentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} /*catch (InaccessibleEiEntryException $e) {
		throw new ForbiddenException(null, 0, $e);
		}*/
	}



	function lookupPreviewController(string $previewType, $eiObjectArg) {
		try {
			return $this->frame()->lookupPreviewController($previewType, $eiObjectArg);
		} catch (UnavailablePreviewException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
	}

	function redirectToOverview(int $status = null) {
		$this->httpContext->getResponse()->send(
				new Redirect($this->frame()->getEiFrame()->getOverviewUrl($this->httpContext), $status));
	}


	private function forwardHtml() {
		if ('text/html' == $this->httpContext->getRequest()->getAcceptRange()
						->bestMatch(['text/html', 'application/json'])) {
			$this->cu->forward('\rocket\core\view\anglTemplate.html');
			return true;
		}

		return false;
	}

	function forwardCompactExplorerZone(int $pageSize = 30, string $title = null) {
		if ($this->forwardHtml()) {
			return;
		}

		$eiFrame = $this->frame()->getEiFrame();
		$eiGuiModel =  $eiFrame->getContextEiEngine()->getEiMask()->getEiEngine()
				->obtainEiGuiModel(ViewMode::COMPACT_READ, null, true);
		$eiGui = new EiGui($eiGuiModel);

		$this->composeEiuGuiForList($eiGui, $pageSize);

		$siComp = (new EiGuiUtil($eiGui, $eiFrame))->createCompactExplorerSiGui($pageSize, true, true, []);

		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($siComp,
						$this->opState->getBreadcrumbs(),
						$title ?? $this->frame()->contextEngine()->mask()->getPluralLabel()));
	}


	private function composeEiuGuiForList($eiGui, $limit) {

		$eiType = $this->frame()->getEiFrame()->getContextEiEngine()->getEiMask()->getEiType();

		$criteria = $this->frame()->getEiFrame()->createCriteria(NestedSetUtils::NODE_ALIAS, false);
		$criteria->select(NestedSetUtils::NODE_ALIAS)->limit($limit);

		if (null !== ($nestedSetStrategy = $eiType->getNestedSetStrategy())) {
			$this->treeLookup($eiGui, $criteria, $nestedSetStrategy);
		} else {
			$this->simpleLookup($eiGui, $criteria);
		}
	}

	private function simpleLookup(EiGui $eiGui, Criteria $criteria) {
		$eiFrame = $this->frame()->getEiFrame();
		$eiFrameUtil = new EiFrameUtil($eiFrame);
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$eiObject = new LiveEiObject($eiFrameUtil->createEiEntityObj($entityObj));
			$eiGui->appendEiEntryGui($eiFrame, [$eiFrame->createEiEntry($eiObject)]);
		}
	}

	private function treeLookup(EiGui $eiGui, Criteria $criteria, NestedSetStrategy $nestedSetStrategy) {
		$nestedSetUtils = new NestedSetUtils($this->frame()->em(), $this->frame()->getContextEiType()->getEntityModel()->getClass(), $nestedSetStrategy);

		$eiFrame = $this->frame()->getEiFrame();
		$eiFrameUtil = new EiFrameUtil($eiFrame);
		foreach ($nestedSetUtils->fetch(null, false, $criteria) as $nestedSetItem) {
			$eiObject = new LiveEiObject($eiFrameUtil->createEiEntityObj($nestedSetItem->getEntityObj()));
			$eiGui->appendEiEntryGui($eiFrame, [$eiFrame->createEiEntry($eiObject)], $nestedSetItem->getLevel());
		}
	}

	function forwardBulkyEntryZone($eiEntryArg, bool $readOnly, bool $generalSiControlsIncluded,
			bool $entrySiControlsIncluded = true, array $generalGuiControls = []): void {
		if ($this->forwardHtml()) {
			return;
		}

		$eiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($eiEntryArg, $this->frame(), 'eiEntryArg', true);
		$eiuGui = $eiuEntry->newGui(true, $readOnly);

		if (null !== ($siResult = $this->handleEiSiCall($eiuGui->getEiGui()->getEiEntryGui(), $generalGuiControls))) {
			$this->cu->sendJson($siResult);
			return;
		}

		$siComp = $eiuGui->createBulkyEntrySiGui($generalSiControlsIncluded, $entrySiControlsIncluded, $generalGuiControls);

		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($siComp,
						$this->opState->getBreadcrumbs(),
						$eiuEntry->createIdentityString()));
	}

	function forwardNewBulkyEntryZone(bool $editable = true, bool $generalSiControlsIncluded = true, bool $entrySiControlsIncluded = true,
			array $generalGuiControls = []): void {
		if ($this->forwardHtml()) {
			return;
		}

		$eiFrame = $this->frame()->getEiFrame();
		$eiFrameUtil = new EiFrameUtil($eiFrame);

		$eiGui = $eiFrameUtil->createNewEiGui(true, !$editable, null, null, true);
		$eiGuiUtil = new EiGuiUtil($eiGui, $eiFrame);

		if (null !== ($siResult = $this->handleEiSiCall($eiGui->getEiEntryGui(), $generalGuiControls))) {
			$this->cu->sendJson($siResult);
			return;
		}

		$siComp = $eiGuiUtil->createBulkyEntrySiGui($generalSiControlsIncluded, $entrySiControlsIncluded, $generalGuiControls);

		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($siComp,
						$this->opState->getBreadcrumbs(),
						$this->eiu->dtc('rocket')->t('common_new_entry_label')));
	}

	function forwardIframeZone(UiComponent $uiComponent, bool $useTemplate = true, string $title = null) {
		if ($this->forwardHtml()) {
			return;
		}

		$iframeSiGui = null;
		if ($useTemplate) {
			$iframeSiGui = new IframeSiGui(IframeData::createFromUiComponentWithTemplate($uiComponent, $this->eiu->getN2nContext()));
		} else {
			$iframeSiGui = new IframeSiGui(IframeData::createFromUiComponent($uiComponent));
		}

		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($iframeSiGui,
						$this->opState->getBreadcrumbs(),
						$title ?? 'Iframe'));
	}

	function forwardIframeUrlZone(Url $url, string $title = null) {
		if ($this->forwardHtml()) {
			return;
		}

		$iframeSiGui = new IframeSiGui(IframeData::createFromUrl($url));

		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($iframeSiGui,
						$this->opState->getBreadcrumbs(),
						$title ?? 'Iframe'));
	}

	/**
	 * @param EiEntryGui $eiEntryGui
	 * @param GuiControl[] $guiControls
	 * @return null|\rocket\si\control\SiCallResponse
	 */
	private function handleEiSiCall(?EiEntryGui $eiEntryGui, array $generalGuiControls) {
		if (!($this->cu->getRequest()->getMethod() === Method::POST && isset($_POST['apiCallId']))) {
			return null;
		}

		$process = new ZoneApiControlProcess($this->frame()->getEiFrame());
		$process->provideEiEntryGui($eiEntryGui);
		$process->determineGuiControl(ZoneApiControlCallId::parse((new ParamPost($_POST['apiCallId']))->parseJson()), $generalGuiControls);

		if (isset($_POST['entryInputMaps'])
				&& null !== ($siInputError = $process->handleInput((new ParamPost($_POST['entryInputMaps']))->parseJson()))) {
			return SiCallResult::fromInputError($siInputError);
		}

		return SiCallResult::fromCallResponse($process->callGuiControl(),
				(isset($_POST['entryInputMaps']) ? $process->createSiInputResult() : null));
	}

	function forwardUrlIframeZone(Url $url, string $title = null) {
		if ($this->forwardHtml()) {
			return;
		}

		$iframeSiGui = new IframeSiGui(IframeData::createFromUrl($url));

		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($iframeSiGui,
						$this->opState->getBreadcrumbs(),
						$title ?? 'Iframe'));
	}

	public function pushBreadcrumb(SiNavPoint $navPoint, string $label): static {
		$this->opState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));
		return $this;
	}

	public function pushSirefBreadcrumb(Url $url, string $label): static  {
		$this->opState->addBreadcrumb(new SiBreadcrumb(SiNavPoint::siref($url), $label));
		return $this;
	}

	public function pushOverviewBreadcrumb(string $label = null, bool $required = false): static {
		$navPoint = $this->frame()->getOverviewNavPoint($required);

		if ($navPoint === null) {
			return $this;
		}

		if ($label === null) {
			$label = $this->frame()->getEiFrame()->getContextEiEngine()->getEiMask()->getPluralLabelLstr()
					->t($this->eiu->getN2nLocale());
		}

		$this->opState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));

		return $this;
	}

	public function pushDetailBreadcrumb($eiObjectArg, string $label = null, bool $required = false): static {
		$eiFrame = $this->frame()->getEiFrame();
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, '$eiObjectArg',
				$eiFrame->getContextEiEngine()->getEiMask()->getEiType());

		$navPoint = $eiFrame->getDetailNavPoint($eiObject, $required);

		if ($navPoint === null) {
			return $this;
		}

		if ($label === null) {
			$label = (new EiFrameUtil($eiFrame))->createIdentityString($eiObject);
		}

		$this->opState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));

		return $this;
	}

	public function pushEditBreadcrumb($eiObjectArg, string $label = null, bool $required = false): static {
		$eiFrame = $this->frame()->getEiFrame();
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, '$eiObjectArg',
				$eiFrame->getContextEiEngine()->getEiMask()->getEiType());

		$navPoint = $eiFrame->getEditNavPoint($eiObject, $required);

		if ($navPoint === null) {
			return $this;
		}

		if ($label === null) {
			$label = (new DynamicTextCollection('rocket', $this->eiu->getN2nContext()->getN2nLocale()))
					->t('common_edit_label');
		}

		$this->opState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));

		return $this;
	}

	public function pushAddBreadcrumb(string $label = null, bool $required = false): static {
		$navPoint = $this->frame()->getAddNavPoint($required);

		if ($navPoint === null) {
			return $this;
		}

		if ($label === null) {
			$label = (new DynamicTextCollection('rocket', $this->eiu->getN2nContext()->getN2nLocale()))
					->t('common_add_label');
		}

		$this->opState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));

		return $this;
	}

	/**
	 * @param string $label
	 * @param bool $includeOverview
	 * @param mixed $detailEiEntryArg
	 * @return OpuCtrl
	 */
	function pushCurrentAsSirefBreadcrumb(string $label, bool $includeOverview = false, $detailEiEntryArg = null) : static{
		if ($includeOverview) {
			$this->pushOverviewBreadcrumb();
		}

		if ($detailEiEntryArg !== null) {
			$this->pushDetailBreadcrumb($detailEiEntryArg);
		}

		$this->pushSirefBreadcrumb($this->httpContext->getRequest()->getUrl(), $label);

		return $this;
	}

	function cuu(): Cuu {
		return $this->cuu;
	}

//	private function forwardHtml(): bool {
//		if ('text/html' == $this->cu->getRequest()->getAcceptRange()
//						->bestMatch(['text/html', 'application/json'])) {
//			$this->cu->forward('\rocket\core\view\anglTemplate.html');
//			return true;
//		}
//
//		return false;
//	}

	/**
	 * @throws CorruptedSiInputDataException
	 */
	private function handleCuSiCall(?CuGui $cuGui): ?SiCallResult {
		$apiCallIdParam = $this->cu->getParamPost('apiCallId');
		if (!($this->cu->getRequest()->getMethod() === Method::POST && null !== $apiCallIdParam)) {
			return null;
		}

		$siInputResult = null;
		if (null !== ($entryInputMapsParam = $this->cu->getParamPost('entryInputMaps'))) {
			$siInput = (new SiInputFactory())->create($entryInputMapsParam->parseJson());
			if (null !== ($siInputError = $cuGui->handleSiInput($siInput, $this->cu->getN2nContext()))) {
				return SiCallResult::fromInputError($siInputError);
			}

			$siInputResult = new SiInputResult($cuGui->getInputSiValueBoundaries());
		}

		return SiCallResult::fromCallResponse(
				$cuGui->handleCall(CuControlCallId::parse($apiCallIdParam->parseJson()), $this->cuu),
				$siInputResult);
	}

	function forwardZone(CufGui|CuGui $gui, string $title): void {
		if ($this->forwardHtml()) {
			return;
		}

		if ($gui instanceof CufGui) {
			$gui = $gui->getCuGui();
		}

		try {
			if (null !== ($siResult = $this->handleCuSiCall($gui))) {
				$this->cu->sendJson($siResult);
				return;
			}
		} catch (CorruptedSiInputDataException $e) {
			throw new BadRequestException('Could not handle SiCall: ' . $e->getMessage(), previous: $e);
		}

		if ($gui instanceof CuGui) {
			$gui = $gui->toSiGui($this->cu->getRequest()->getPath()->toUrl());
		}

		$this->cu->send(SiPayloadFactory::create($gui, $this->opState->getBreadcrumbs(), $title));
	}

	public static function from(ControllingUtils $cu): OpuCtrl {
		return new OpuCtrl($cu);
	}
}