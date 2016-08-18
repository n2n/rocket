<?php
namespace rocket\script\entity\field\impl\date;

use rocket\script\entity\preview\PreviewModel;
use n2n\dispatch\PropertyPath;
use rocket\script\entity\preview\PreviewableScriptField;
use n2n\persistence\orm\property\DateTimeProperty;
use n2n\l10n\L10nUtils;
use n2n\dispatch\option\impl\DateTimeOption;
use n2n\l10n\DateTimeFormat;
use n2n\dispatch\option\impl\EnumOption;
use n2n\l10n\Locale;
use rocket\script\entity\manage\ScriptState;
use n2n\persistence\orm\Entity;
use n2n\ui\html\HtmlView;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\field\HighlightableScriptField;
use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use rocket\script\entity\field\SortableScriptField;
use n2n\core\N2nContext;
use rocket\script\entity\filter\item\SimpleSortItem;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\field\impl\ManageInfo;

class DateTimeScriptField extends TranslatableScriptFieldAdapter implements HighlightableScriptField, PreviewableScriptField, SortableScriptField {
	const OPTION_DATE_STYLE = 'dateStyle';
	const OPTION_TIME_STYLE = 'timeStyle';
	
	public function getTypeName() {
		return 'DateTime';
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DateTimeProperty; 
	}
	
	public function getDateStyle() {
		return $this->getAttributes()->get(self::OPTION_DATE_STYLE);
	}
	
	public function getTimeStyle() {
		return $this->getAttributes()->get(self::OPTION_TIME_STYLE);
	}
	
	public function createOptionCollection() {
		$styles = DateTimeFormat::getStyles();
		$optionStyles = array_combine($styles, $styles);
		$optionCollection = parent::createOptionCollection();
 		$optionCollection->addOption(self::OPTION_DATE_STYLE, new EnumOption('Date Style', 
 				$optionStyles, DateTimeFormat::DEFAULT_DATE_STYLE, true));
 		$optionCollection->addOption(self::OPTION_TIME_STYLE, new EnumOption('Time Style', 
 				$optionStyles, DateTimeFormat::DEFAULT_TIME_STYLE, true));
		return $optionCollection;
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view, ManageInfo $manageInfo)  {
		return $view->getHtmlBuilder()->getL10nDateTime($scriptSelectionMapping->getValue($this->getId()), 
				$this->getDateStyle(), $this->getTimeStyle());
	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return new DateTimeOption($this->getLabel(), $this->getDateStyle(), $this->getTimeStyle(), null, null, 
				$this->isRequired($scriptSelectionMapping, $manageInfo), array('placeholder' => $this->getLabel(), 
						'data-icon-class-name-open' => IconType::ICON_CALENDAR,
						'class' => 'rocket-date-picker'));
	}
	
	public function createKnownString(Entity $entity, Locale $locale) {
		if (null !== ($dateTime = $this->getPropertyAccessProxy()->getValue($entity))) {
			return L10nUtils::formatDateTime($locale, $dateTime,
					$this->getDateStyle(), $this->getTimeStyle());
		}
		
		return null;
	}
	
	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
		return $view->getFormHtmlBuilder()->getInputField($propertyPath, array('class' => 'rocket-preview-inpage-component'));
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\SortableScriptField::createSortCriteriaConstraint()
	 */
	public function createSortItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new SimpleSortItem($this->getEntityProperty()->getName(), $this->getLabel());
	}

}