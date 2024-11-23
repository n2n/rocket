<?php

namespace rocket\ui\gui\res;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\PageNotFoundException;
use n2n\context\attribute\Inject;
use n2n\web\http\nav\Murl;
use rocket\core\controller\RocketController;
use n2n\core\container\N2nContext;
use rocket\op\OpState;

class GuiResourceController extends ControllerAdapter  {

	#[Inject]
	private GuiResourceRegistry $registry;

	/**
	 * @throws PageNotFoundException
	 */
	function doFile(string $fileAccessToken): void {
		$file = $this->registry->lookupFile($fileAccessToken);
		if ($file === null) {
			throw new PageNotFoundException();
		}

		$this->sendFile($file);
	}

	static function determineFileUrl(string $fileAccessToken, N2nContext $n2nContext): \n2n\util\uri\Url {
		return $n2nContext->lookup(OpState::class)->getGuiResourceUrl()
				->pathExt('file', $fileAccessToken);
	}
}