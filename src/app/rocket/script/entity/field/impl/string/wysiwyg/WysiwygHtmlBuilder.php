<?php
namespace rocket\script\entity\field\impl\string\wysiwyg;

use n2n\util\StringUtils;
use rocket\script\entity\field\impl\string\wysiwyg\bbcode\definitionset\PhpbbDefinitionSet;
use rocket\script\entity\field\impl\string\wysiwyg\bbcode\Parser;
use n2n\ui\Raw;
use n2n\ui\html\HtmlUtils;
use n2n\ui\html\HtmlView;
use n2n\model\ModelRuntimeException;

class WysiwygHtmlBuilder {
	
	const ATTRIBUTE_TOOLBAR = 'data-toolbar';
	const ATTRIBUTE_BBCODE = 'data-bbcode';
	const ATTRIBUTE_TABLE_EDITING = 'data-table-editing';
	const ATTRIBUTE_BODY_ID = 'data-body-id';
	const ATTRIBUTE_BODY_CLASS = 'data-body-class';
	const ATTRIBUTE_CONTENTS_CSS= 'data-contents-css';
	const ATTRIBUTE_ADDITIONAL_STYLES = 'data-additional-styles';
	const ATTRIBUTE_FORMAT_TAGS = 'data-format-tags';
	const ATTRIBUTE_LINK_CONFIGURATIONS = 'data-link-configurations';
	
	const MODE_SIMPLE = 'simple';
	const MODE_NORMAL = 'normal';
	const MODE_ADVANCED = 'advanced';
	
	const CLASS_NAME_WYSIWYG = 'rocket-wysiwyg';
	const CLASS_NAME_WYSIWYG_INLINE = 'rocket-preview-inpage-wysiwyg';
	
	const DYNAMIC_LINK_CONFIG_REF = 'ref';
	const DYNAMIC_LINK_CONFIG_CHARACTERISTICS = 'characteristics';
	
	private $html;
	private $view;
	
	public function __construct(HtmlView $view) {
		$this->view = $view;
		$this->html = $view->getHtmlBuilder();
	}
	
	public function wysiwygIframeBbcode($bbcode, WysiwygCssConfiguration $cssConfiguration = null) {
		echo $this->getWysiwygIframeBbcode($bbcode, $cssConfiguration);
	}
	
	public function getWysiwygIframeBbcode($bbcode, WysiwygCssConfiguration $cssConfiguration = null) {
		$bbcodeParser = new Parser();
		$bbcodeParser->addCodeDefinitionSet(new PhpbbDefinitionSet());
		$bbcodeParser->parse(htmlspecialchars($bbcode));
		$html = $this->getWysiwygContent($bbcodeParser->getAsHTML());
		return $this->getWysiwygIframeHtml($html, $cssConfiguration);
	} 
	
	public function getWysiwygContent($contentsHtml) {
		$that = $this;
		return new Raw(preg_replace_callback('/href\s*=\s*"({.*?})"/', function($matches) use ($that) {
			$data = StringUtils::jsonDecode(html_entity_decode($matches[1]), true);
			if (null === $data 
					|| !isset($data[self::DYNAMIC_LINK_CONFIG_REF]) 
					|| !isset($data[self::DYNAMIC_LINK_CONFIG_CHARACTERISTICS])) return $matches[0];
			try {
				$dynamicLinkBuilder = $that->view->lookup($data[self::DYNAMIC_LINK_CONFIG_REF]);
				if (!($dynamicLinkBuilder instanceof DynamicLinkBuilder)) return $matches[0];
				
				return 'href="' . $dynamicLinkBuilder->buildLink($this->view->getRequest(), 
						$data[self::DYNAMIC_LINK_CONFIG_CHARACTERISTICS]) . '"';
			} catch (ModelRuntimeException $e) {
				return $matches[0];
			}
		}, $contentsHtml));
	}
	
	public function wysiwygContent($contentsHtml) {
		return $this->view->out($this->getWysiwygContent($contentsHtml));
	}
	
	public function out($contentsHtml) {
		$this->wysiwygContent($contentsHtml);
	}
	
	public function getWysiwygIframeHtml($html, WysiwygCssConfiguration $cssConfiguration = null) {
// 		
// 		$this->htmlBuilder->addLibrary(new JQueryLibrary());
		$this->html->addJs('js/wysiwyg.js', 'rocket');
		
		$headLinkHtml = '';
		$bodyIdHtml = '';
		$bodyClassHtml = '';
		if ($cssConfiguration) {
			$headLinkHtml = str_replace('"', '\'', StringUtils::jsonEncode((array) $cssConfiguration->getContentCssPaths($this->html->getView())));
			$bodyIdHtml = ($bodyId = $cssConfiguration->getBodyId()) ?  self::ATTRIBUTE_BODY_ID . '="' . $bodyId . '"' : '';
			$bodyClassHtml = ($bodyClass = $cssConfiguration->getBodyClass()) ? ' ' . self::ATTRIBUTE_BODY_CLASS . '="' . $bodyClass . '"' : '';
		}
		return new Raw('<div class="rocket-wysiwyg-content" style="display:none">' 
				. $this->getWysiwygContent($html) . '</div><iframe scrolling="auto"' . $bodyIdHtml . ' class="rocket-wysiwyg-detail" ' . $bodyClassHtml 
				. ' ' . self::ATTRIBUTE_CONTENTS_CSS . '="' . $headLinkHtml . '"></iframe>');
	}
	
	public function wysiwygIframe($value, $bbcode = false, WysiwygCssConfiguration $cssConfiguration = null) {
		echo $this->getWysiwygIframe($value, $bbcode, $cssConfiguration);
	}
	
	public function getWysiwygEditor($propertyPath = null, $mode = self::MODE_NORMAL, $isBbCode = false, 
			$inline = false, $tableEditing = false, array $linkConfigurations = null, 
			WysiwygCssConfiguration $cssConfiguration = null, array $attrs = null) {
		
		$this->html->addLibrary(new WysiwygLibrary());
		$wysiwygClassName = $inline ? self::CLASS_NAME_WYSIWYG_INLINE : self::CLASS_NAME_WYSIWYG;

		$attrs = HtmlUtils::mergeAttrs((array) $attrs, array('class' => $wysiwygClassName, self::ATTRIBUTE_TOOLBAR => $mode,
				self::ATTRIBUTE_BBCODE => $isBbCode, self::ATTRIBUTE_TABLE_EDITING => $tableEditing));
		
		if ($cssConfiguration) {
			$attrs[self::ATTRIBUTE_BODY_CLASS] = $cssConfiguration->getBodyClass();
			$attrs[self::ATTRIBUTE_BODY_ID] = $cssConfiguration->getBodyId();
			if (null != ($cssPaths = $cssConfiguration->getContentCssPaths($this->html->getView())) && count($cssPaths) > 0) {
				$attrs[self::ATTRIBUTE_CONTENTS_CSS] = str_replace('"', '\'', StringUtils::jsonEncode((array) $cssPaths));
			}
			$attrs[self::ATTRIBUTE_ADDITIONAL_STYLES] = StringUtils::jsonEncode($this->prepareAdditionalStyles($cssConfiguration->getAdditionalStyles()));
			$attrs[self::ATTRIBUTE_FORMAT_TAGS] = implode(';', (array) $cssConfiguration->getFormatTags());
		}
		
		if (count($linkConfigurations) > 0) {
			$attrs[self::ATTRIBUTE_LINK_CONFIGURATIONS] = StringUtils::jsonEncode($this->prepareLinkConfigurations($linkConfigurations));
		}
		$this->html->addJs('js/wysiwyg.js', 'rocket');
		return $this->html->getView()->getFormHtmlBuilder()->getTextarea($propertyPath, $attrs);
	}
	
	public function wysiwygEditor($propertyPath = null, $mode = self::MODE_NORMAL, $isBbCode = false, 
			$isInline = false, $tableEditing = false, array $linkConfigurations = null, 
			WysiwygCssConfiguration $cssConfiguration = null, array $attrs = null) {
		echo $this->getWysiwygEditor($propertyPath, $mode, $isBbCode, $isInline, $tableEditing, $linkConfigurations, $cssConfiguration, $attrs);
	}
	
	private function prepareAdditionalStyles($additionalStyles) {
		$encodable = array();
		foreach ((array) $additionalStyles as $style) {
			$style instanceof WysiwygStyle;
			$encodable[] = $style->getValueForJsonEncode();
		}
		return $encodable;
	}
	
	private function prepareLinkConfigurations($linkConfigurations) {
		$preparedLinkConfigurations = array();
		foreach ($linkConfigurations as $linkConfiguration) {
			$linkConfiguration instanceof WysiwygLinkConfiguration;
			$title = $linkConfiguration->getTitle();
			$preparedLinkConfigurations[$title] = array();
			$preparedLinkConfigurations[$title]['items'] = array();
			$preparedLinkConfigurations[$title]['open-in-new-window'] = $linkConfiguration->isOpenInNewWindow();
			foreach ($linkConfiguration->getLinkPaths() as $pathTitle => $url) {
				if ($linkConfiguration instanceof DynamicWysiwygLinkConfiguration) {
					if (null !== ($urlData = json_decode($url, true))) {
						$url = $urlData;
					}
					$url = StringUtils::jsonEncode(
							array(self::DYNAMIC_LINK_CONFIG_REF => $linkConfiguration->getLinkBuilderClass()->getName(), 
									self::DYNAMIC_LINK_CONFIG_CHARACTERISTICS => $url));
 				}
				$preparedLinkConfigurations[$title]['items'][] = array($pathTitle, $url);
			}
		}
		return $preparedLinkConfigurations;
	}
}