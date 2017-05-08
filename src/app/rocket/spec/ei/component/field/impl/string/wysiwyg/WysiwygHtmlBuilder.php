<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\spec\ei\component\field\impl\string\wysiwyg;

use n2n\util\StringUtils;
use rocket\spec\ei\component\field\impl\string\wysiwyg\bbcode\definitionset\PhpbbDefinitionSet;
use rocket\spec\ei\component\field\impl\string\wysiwyg\bbcode\Parser;
use n2n\web\ui\Raw;
use n2n\impl\web\ui\view\html\HtmlUtils;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\context\LookupFailedException;
use n2n\l10n\N2nLocale;

class WysiwygHtmlBuilder {
	
	const ATTRIBUTE_TOOLBAR = 'data-toolbar';
	const ATTRIBUTE_BBCODE = 'data-bbcode';
	const ATTRIBUTE_TABLE_EDITING = 'data-table-editing';
	const ATTRIBUTE_BODY_ID = 'data-body-id';
	const ATTRIBUTE_BODY_CLASS = 'data-body-class';
	const ATTRIBUTE_CONTENTS_CSS = 'data-contents-css';
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
	
	public function wysiwygIframeBbcode($bbcode, WysiwygCssConfig $cssConfiguration = null) {
		echo $this->getWysiwygIframeBbcode($bbcode, $cssConfiguration);
	}
	
	public function getWysiwygIframeBbcode($bbcode, WysiwygCssConfig $cssConfiguration = null) {
		$bbcodeParser = new Parser();
		$bbcodeParser->addCodeDefinitionSet(new PhpbbDefinitionSet());
		$bbcodeParser->parse(htmlspecialchars($bbcode));
		$html = $this->getWysiwygContent($bbcodeParser->getAsHTML());
		return $this->getWysiwygIframeHtml($html, $cssConfiguration);
	} 
	
	public function getWysiwygContent($contentsHtml) {
		$that = $this;
		return new Raw(preg_replace_callback('/href\s*=\s*"({.*?})"/', function($matches) use ($that) {
			$data = json_decode(html_entity_decode($matches[1]), true);
			if (null === $data 
					|| !isset($data[self::DYNAMIC_LINK_CONFIG_REF]) 
					|| !isset($data[self::DYNAMIC_LINK_CONFIG_CHARACTERISTICS])) return $matches[0];
			try {
				$dynamicLinkBuilder = $that->view->lookup($data[self::DYNAMIC_LINK_CONFIG_REF]);
				if (!($dynamicLinkBuilder instanceof DynamicUrlBuilder)) return $matches[0];
				
				return 'href="' . $dynamicLinkBuilder->buildUrl($this->view->getHttpContext(), 
						$data[self::DYNAMIC_LINK_CONFIG_CHARACTERISTICS]) . '"';
			} catch (LookupFailedException $e) {
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
	
	public function getWysiwygIframeHtml($html, WysiwygCssConfig $cssConfig = null) {
// 		
// 		$this->htmlBuilder->addLibrary(new JQueryLibrary());
		$this->html->meta()->addJs('js/wysiwyg.js', 'rocket');
		
		$headLinkHtml = '';
		$bodyIdHtml = '';
		$bodyClassHtml = '';
		if ($cssConfig) {
			$headLinkHtml = str_replace('"', '\'', StringUtils::jsonEncode((array) $this->getCssPaths($cssConfig)));
			$bodyIdHtml = ($bodyId = $cssConfig->getBodyId()) ?  self::ATTRIBUTE_BODY_ID . '="' . $bodyId . '"' : '';
			$bodyClassHtml = ($bodyClass = $cssConfig->getBodyClass()) ? ' ' . self::ATTRIBUTE_BODY_CLASS . '="' . $bodyClass . '"' : '';
		}
		
		return new Raw('<div class="rocket-wysiwyg-content" style="display:none">' 
				. $this->getWysiwygContent($html) . '</div><iframe scrolling="auto" ' . $bodyIdHtml . ' class="rocket-wysiwyg-detail" ' . $bodyClassHtml 
				. ' ' . self::ATTRIBUTE_CONTENTS_CSS . '="' . $headLinkHtml . '"></iframe>');
	}
	
	public function wysiwygIframe($value, $bbcode = false, WysiwygCssConfig $cssConfiguration = null) {
		echo $this->getWysiwygIframe($value, $bbcode, $cssConfiguration);
	}
	
	public function getWysiwygEditor($propertyPath = null, $mode = self::MODE_NORMAL, $isBbCode = false, 
			$inline = false, $tableEditing = false, array $linkConfigurations = null, 
			WysiwygCssConfig $cssConfig = null, array $attrs = null, N2nLocale $n2nLocale = null) {
		
		$this->html->meta()->addLibrary(new WysiwygLibrary());
		$wysiwygClassName = $inline ? self::CLASS_NAME_WYSIWYG_INLINE : self::CLASS_NAME_WYSIWYG;

		$attrs = HtmlUtils::mergeAttrs((array) $attrs, array('class' => $wysiwygClassName, self::ATTRIBUTE_TOOLBAR => $mode,
				self::ATTRIBUTE_BBCODE => $isBbCode, self::ATTRIBUTE_TABLE_EDITING => $tableEditing));
		
		if ($cssConfig) {
			$attrs[self::ATTRIBUTE_BODY_CLASS] = $cssConfig->getBodyClass();
			$attrs[self::ATTRIBUTE_BODY_ID] = $cssConfig->getBodyId();
			if (!empty($cssPaths = $this->getCssPaths($cssConfig))) {
				$attrs[self::ATTRIBUTE_CONTENTS_CSS] = str_replace('"', '\'', StringUtils::jsonEncode((array) $cssPaths));
			}
			$attrs[self::ATTRIBUTE_ADDITIONAL_STYLES] = StringUtils::jsonEncode($this->prepareAdditionalStyles($cssConfig->getAdditionalStyles()));
			$attrs[self::ATTRIBUTE_FORMAT_TAGS] = implode(';', (array) $cssConfig->getFormatTags());
		}
		
		if (count($linkConfigurations) > 0) {
			$attrs[self::ATTRIBUTE_LINK_CONFIGURATIONS] = StringUtils::jsonEncode(
					$this->prepareLinkConfigurations($linkConfigurations, $n2nLocale));
		}
		$this->html->meta()->addJs('js/wysiwyg.js', 'rocket');
		return $this->view->getFormHtmlBuilder()->getTextarea($propertyPath, $attrs);
	}
	
	public function wysiwygEditor($propertyPath = null, $mode = self::MODE_NORMAL, $isBbCode = false, 
			$isInline = false, $tableEditing = false, array $linkConfigurations = null, 
			WysiwygCssConfig $cssConfiguration = null, array $attrs = null) {
		echo $this->getWysiwygEditor($propertyPath, $mode, $isBbCode, $isInline, $tableEditing, $linkConfigurations, $cssConfiguration, $attrs);
	}
	
	private function getCssPaths(WysiwygCssConfig $cssConfig) {
		if (empty($cssPaths = $cssConfig->getContentCssPaths($this->view))) return array();
		
		$tmpCssPaths = array();
		foreach ($cssPaths as $cssPath) {
			$tmpCssPaths[] = (string) $cssPath;
		}
		return $tmpCssPaths;
	}
	
	private function prepareAdditionalStyles($additionalStyles) {
		$encodable = array();
		foreach ((array) $additionalStyles as $style) {
			$style instanceof WysiwygStyle;
			$encodable[] = $style->getValueForJsonEncode();
		}
		return $encodable;
	}
	
	private function prepareLinkConfigurations($linkConfigurations, N2nLocale $n2nLocale = null) {
		$n2nLocale = (null !== $n2nLocale) ? $n2nLocale : $this->view->getN2nContext()->getN2nLocale();
		$preparedLinkConfigurations = array();
		foreach ($linkConfigurations as $linkConfiguration) {
			$linkConfiguration instanceof WysiwygLinkConfig;
			$title = $linkConfiguration->getTitle();
			$preparedLinkConfigurations[$title] = array();
			$preparedLinkConfigurations[$title]['items'] = array();
			$preparedLinkConfigurations[$title]['open-in-new-window'] = $linkConfiguration->isOpenInNewWindow();
			foreach ($linkConfiguration->getLinkPaths($n2nLocale) as $pathTitle => $url) {
				if ($linkConfiguration instanceof DynamicWysiwygLinkConfig) {
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
	
	public static function getModes() {
		return array(self::MODE_SIMPLE, self::MODE_NORMAL, self::MODE_ADVANCED);
	}
}
