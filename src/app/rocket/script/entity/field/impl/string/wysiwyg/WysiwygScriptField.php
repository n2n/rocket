<?php
namespace rocket\script\entity\field\impl\string\wysiwyg;

use rocket\script\entity\preview\PreviewModel;
use n2n\dispatch\option\impl\StringArrayOption;
use n2n\reflection\ReflectionUtils;
use n2n\reflection\ReflectionContext;
use n2n\dispatch\option\impl\EnumOption;
use n2n\dispatch\PropertyPath;
use n2n\dispatch\option\impl\BooleanOption;
use rocket\script\entity\manage\ScriptState;
use n2n\dispatch\option\impl\StringOption;
use n2n\ui\Raw;
use n2n\ui\html\HtmlView;
use rocket\script\entity\preview\PreviewableScriptField;
use rocket\script\entity\field\impl\string\AlphanumericScriptField;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use rocket\script\entity\field\impl\ManageInfo;
use n2n\util\Attributes;
use n2n\core\TypeNotFoundException;

class WysiwygScriptField extends AlphanumericScriptField implements PreviewableScriptField {
	
	const OPTION_LINK_CONFIG_KEY = 'linkConfig';
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		
		$this->displayInListViewDefault = false;
		$this->optionRequiredDefault = false;
	}
	
	public function getTypeName() {
		return 'Wysiwyg';
	}
	
	public function getLinkConfigClassNames() {
		return $this->attributes->get(self::OPTION_LINK_CONFIG_KEY);
	}

	public function createOptionCollection() {
		$optionCollection = new OptionCollectionImpl();
		$this->applyDisplayOptions($optionCollection, false);
		$this->applyEditOptions($optionCollection);
		$this->applyTranslationOptions($optionCollection);
		$this->applyDraftOptions($optionCollection);
		
		$optionCollection->addOption('mode', new EnumOption('Mode', 
				array(WysiwygHtmlBuilder::MODE_SIMPLE => 'simple', WysiwygHtmlBuilder::MODE_NORMAL => 'normal', WysiwygHtmlBuilder::MODE_ADVANCED => 'advanced')));
		$optionCollection->addOption(self::OPTION_LINK_CONFIG_KEY, new StringArrayOption('Link Configuration'));
		$optionCollection->addOption('cssConfig', new StringOption('Css Configuration'));
		$optionCollection->addOption('tableEditing', new BooleanOption('Table Editing'));
		$optionCollection->addOption('bbcode', new BooleanOption('BBcode'));
		return $optionCollection;
	}

	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view, ManageInfo $manageInfo)  {
		$value = $scriptSelectionMapping->getValue($this->id);
		$wysiwygHtml = new WysiwygHtmlBuilder($view);
		if ($this->getAttributes()->get('bbcode')) {
			return $wysiwygHtml->getWysiwygIframeBbcode($value, $this->obtainCssConfiguration());
		}
		return $wysiwygHtml->getWysiwygIframeHtml($value, $this->obtainCssConfiguration());
		
	}
	
	public function createPreviewUiComponent(ScriptState $scriptState = null, HtmlView $view, $value) {
		return new Raw($value);
	}

	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
		$wysiwygHtml = new WysiwygHtmlBuilder($view);
		return $wysiwygHtml->getWysiwygEditor($propertyPath, $this->getAttributes()->get('mode'),
				$this->getAttributes()->get('bbcode'), true, $this->getAttributes()->get('tableEditing'), $this->obtainLinkConfigurations(), 
				$this->obtainCssConfiguration(), array('class' => 'rocket-preview-inpage-component'));
	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return new WysiwygOption($this->getLabel(), null,
				$this->isRequired($scriptSelectionMapping, $manageInfo), 
				$this->getAttributes()->get('maxlength'), null, 
				$this->getAttributes()->get('mode'), $this->getAttributes()->get('bbcode'),
				$this->getAttributes()->get('tableEditing'), 
				$this->obtainLinkConfigurations($scriptSelectionMapping, $manageInfo), 
				$this->obtainCssConfiguration());
	}
	/**
	 * @return \rocket\script\entity\field\WysiwygLinkConfiguration
	 */
	private function obtainLinkConfigurations(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$n2nContext = $manageInfo->getScriptState()->getN2nContext();
		
		$linkConfigurations = array();
		foreach((array) $this->getAttributes()->get(self::OPTION_LINK_CONFIG_KEY) as $linkConfigurationClass) {
			try {
				if (null !== ($reflectionClass = ReflectionUtils::createReflectionClass($linkConfigurationClass))) {
					if ($reflectionClass->implementsInterface('rocket\script\entity\field\impl\string\wysiwyg\WysiwygLinkConfiguration')) {
						$linkConfiguration = $reflectionClass->newInstanceArgs(array($scriptSelectionMapping, $manageInfo));
						$n2nContext->magicInit($linkConfiguration);
						$linkConfigurations[] = $linkConfiguration;
					}
				}
			} catch (TypeNotFoundException $e) {}
		}
		return $linkConfigurations;
	}
	/**
	* @return rocket\script\entity\field\impl\string\wysiwyg\WysiwygCssConfiguration
	*/
	private function obtainCssConfiguration() {
		$cssConfiguration = null;
		if (null != ($cssConfigurationClass = $this->getAttributes()->get('cssConfig'))) {
			try {
				if (null !== ($reflectionClass = ReflectionUtils::createReflectionClass($cssConfigurationClass))) {
					if ($reflectionClass->implementsInterface('rocket\script\entity\field\impl\string\wysiwyg\WysiwygCssConfiguration')) {
						$cssConfiguration = ReflectionContext::createObject($reflectionClass);
					}
				}
			} catch (TypeNotFoundException $e) {}
		}
		return $cssConfiguration;
	}
}