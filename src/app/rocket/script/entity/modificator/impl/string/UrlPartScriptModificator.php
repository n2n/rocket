<?php
namespace rocket\script\entity\modificator\impl\string;

use rocket\script\entity\modificator\impl\ScriptModificatorAdapter;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\string\UrlPartScriptField;
use rocket\script\entity\manage\mapping\OnWriteMappingListener;
use n2n\util\Attributes;
class UrlPartScriptModificator extends ScriptModificatorAdapter {
	
	private $scriptField;
	
	public function __construct(UrlPartScriptField $scriptField) {
		$this->scriptField = $scriptField;
	}
	
	public function setupScriptSelectionMapping(ScriptState $scriptState, ScriptSelectionMapping $ssm) {
		$scriptSelection = $ssm->getScriptSelection();
		if (($scriptSelection->isNew() && !$this->scriptField->isDisplayInAddViewEnabled()) 
				|| (!$scriptSelection->isNew() && !$this->scriptField->isDisplayInEditViewEnabled())) {
			
			$scriptField = $this->scriptField;
			$ssm->registerListener(new OnWriteMappingListener(function() 
					use ($scriptField, $ssm, $scriptState) {
				$scriptSelection = $ssm->getScriptSelection();
				$accessProxy = $scriptField->getPropertyAccessProxy();
				$value = ($scriptSelection->isNew()) ? null : $accessProxy->getValue($scriptSelection->getCurrentEntity());
				$accessProxy->setValue($ssm->getScriptSelection()->getCurrentEntity(), 
					$scriptField->determineUrlPart($scriptState->getEntityManager(), $value, $value, new Attributes($ssm->getValues()), $scriptSelection));
			}));
		}
	}
}