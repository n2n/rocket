<?php
namespace rocket\spec\ei\component\field\impl\translation\command;

use n2n\web\http\controller\Controller;
use rocket\spec\ei\component\command\impl\EiCommandAdapter;
use rocket\spec\ei\manage\util\model\Eiu;

class TranslationCopyCommand extends EiCommandAdapter {
	public function lookupController(Eiu $eiu): Controller {
		return $eiu->lookup(TranslationCopyController::class);
	}

	
}