<?php

namespace rocket\cu\util;

use n2n\web\http\controller\impl\ControllingUtils;
use rocket\cu\gui\CuGui;
use rocket\si\content\SiGui;
use rocket\cu\util\gui\CufGui;
use rocket\si\SiPayloadFactory;
use n2n\web\http\Method;
use rocket\ei\manage\api\ZoneApiControlProcess;
use rocket\ei\manage\api\ZoneApiControlCallId;
use n2n\web\http\controller\ParamPost;
use rocket\ei\manage\api\SiCallResult;
use rocket\si\input\SiInputFactory;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\ei\util\Eiu;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\ManageState;
use rocket\cu\gui\control\CuControlCallId;

class CuuCtrl {

	private Cuu $cuu;

	function __construct(private ControllingUtils $cu) {
		$eiuAnalyst = new EiuAnalyst();
		$eiuAnalyst->applyEiArgs($cu->getN2nContext(),
				$cu->getN2nContext()->lookup(ManageState::class)->peakEiFrame());

		$this->cuu = new Cuu($eiuAnalyst);
	}

	function cuu(): Cuu {
		return $this->cuu;
	}

	private function forwardHtml(): bool {
		if ('text/html' == $this->cu->getRequest()->getAcceptRange()
						->bestMatch(['text/html', 'application/json'])) {
			$this->cu->forward('\rocket\core\view\anglTemplate.html');
			return true;
		}

		return false;
	}

	/**
	 * @throws CorruptedSiInputDataException
	 */
	private function handleSiCall(?CuGui $cuGui, array $generalGuiControls): ?SiCallResult {
		if (!($this->cu->getRequest()->getMethod() === Method::POST && isset($_POST['apiCallId']))) {
			return null;
		}

		$entryInputMaps = $this->cu->getRequest()->getPostQuery()->get('entryInputMaps');
		if (isset($entryInputMaps)) {
			$entryInputMapsParam = new ParamPost($entryInputMaps);
			$siInput = (new SiInputFactory())->create($entryInputMapsParam->parseJson());
			$cuGui->handleSiInput($siInput);
		}

		$apiCallIdData = $this->cu->getRequest()->getPostQuery()->get('apiCallId');
		if (!isset($apiCallIdData)) {
			return null;
		}

		return SiCallResult::fromCallResponse($cuGui->handleCall(),
				(isset($_POST['entryInputMaps']) ? $process->createSiInputResult() : null));
	}

	function forwardZone(CufGui|CuGui|SiGui $gui, string $title): void {
		if ($this->forwardHtml()) {
			return;
		}

		if ($gui instanceof CufGui) {
			$gui = $gui->getCuGui();
		}

		if ($gui instanceof CuGui) {
			$gui = $gui->toSiGui($this->cu->getRequest()->getPath()->toUrl());
		}

		$this->cu->send(SiPayloadFactory::create($gui, [], $title));
	}

	public static function from(ControllingUtils $cu): CuuCtrl {
		return new CuuCtrl($cu);
	}
}