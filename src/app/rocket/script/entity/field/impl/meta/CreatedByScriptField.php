<?php

namespace rocket\script\entity\field\impl\meta;

use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\ui\html\HtmlView;
use rocket\script\entity\field\impl\ManageInfo;
use n2n\persistence\orm\property\DefaultProperty;
use rocket\user\bo\User;
use rocket\script\core\SetupProcess;
use rocket\script\entity\field\impl\meta\model\CreatedByModificator;
use rocket\script\entity\field\impl\EditableScriptFieldAdapter;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use n2n\util\Attributes;
use n2n\core\UnsupportedOperationException;
use rocket\script\entity\field\FilterableScriptField;
use rocket\user\model\RestrictionScriptField;
use n2n\core\N2nContext;
use n2n\reflection\ArgumentUtils;
use rocket\script\entity\field\impl\meta\model\UserFilterItem;
use rocket\user\model\LoginContext;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\field\impl\meta\model\UserSelectorItem;
use n2n\persistence\orm\Entity;
use rocket\script\entity\field\HighlightableScriptField;
use n2n\l10n\Locale;

class CreatedByScriptField extends EditableScriptFieldAdapter implements FilterableScriptField, RestrictionScriptField, HighlightableScriptField {
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		$this->displayInAddViewDefault = false;
		$this->optionReadOnlyDefault = true;
	}
	
	public function createOptionCollection() {
		$optionCollection = new OptionCollectionImpl();
		parent::applyDisplayOptions($optionCollection, false, true, true, true, true);
		return $optionCollection;
	}
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		
		$this->getEntityScript()->getModificatorCollection()->add(new CreatedByModificator($this->id));
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\impl\EntityPropertyScriptFieldAdapter::isCompatibleWith()
	 */
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DefaultProperty;	
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\impl\StatelessDisplayable::createUiOutputField()
	 */
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $htmlView, ManageInfo $manageInfo) {
		$em = $manageInfo->getScriptState()->getEntityManager();
		$userId = $scriptSelectionMapping->getValue($this->id);
		$user = null;
		if ($userId === null || null === ($user = $em->find(User::getClass(), $userId))) {
			return null;
		}
		
		return $htmlView->getHtmlBuilder()->getEsc($user->__toString());
	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		throw new UnsupportedOperationException(__CLASS__ . ' ScriptField ist not editable.');
	}
	
	private function lookupCurrentUserId(N2nContext $n2nContext) {
		$loginContext = $n2nContext->getUsableContext()->lookup('rocket\user\model\LoginContext');
		ArgumentUtils::assertTrue($loginContext instanceof LoginContext);
		$currentUser = $loginContext->getCurrentUser();
		if ($currentUser === null) return null;
		return $currentUser->getId();
	}
	
	public function createFilterItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new UserFilterItem($this->getEntityProperty()->getName(), $this->getLabel(),
				$n2nContext->getLocale(), $this->lookupCurrentUserId($n2nContext));
	}
	
	public function createRestrictionSelectorItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new UserSelectorItem($this->getEntityProperty()->getName(), $this->getLabel(),
				$n2nContext->getLocale(), $this->lookupCurrentUserId($n2nContext));
	}
			
	public function createKnownString(Entity $entity, Locale $locale) {
		return (string) $this->read($entity);
	}
}