<?php
namespace rocket\impl\ei\component\prop\translation\command;

use n2n\web\http\controller\Controller;
use rocket\impl\ei\component\command\EiCommandAdapter;
use rocket\spec\ei\manage\util\model\Eiu;

class TranslationCopyCommand extends EiCommandAdapter {
	public function lookupController(Eiu $eiu): Controller {
		return $eiu->lookup(TranslationCopyController::class);
	}
}