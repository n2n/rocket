<?php
namespace rocket\script\entity\modificator\impl\l10n;

use rocket\script\entity\modificator\impl\ScriptModificatorAdapter;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\mapping\OnWriteMappingListener;
use rocket\script\entity\field\impl\l10n\LocaleScriptField;
use n2n\N2N;

class LocaleScriptModificator extends ScriptModificatorAdapter {
	private $scriptField;
	
	public function __construct(LocaleScriptField $scriptField) {
		$this->scriptField = $scriptField;
	}
	
	public function setupScriptSelectionMapping(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping) {
		if ($this->scriptField->isMultiLingual()) return;
		if (!$scriptSelectionMapping->getScriptSelection()->isNew()) return;
		$that = $this;
		$scriptSelectionMapping->registerListener(new OnWriteMappingListener(function() 
				use ($scriptState, $scriptSelectionMapping, $that) {
			$scriptSelectionMapping->setValue($that->scriptField->getId(), Locale::getDefault());
		}));
	}
}