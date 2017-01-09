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
namespace rocket\spec\ei\component\field\impl\string\cke\ui;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\Raw;
use n2n\impl\web\ui\view\html\HtmlUtils;
use rocket\spec\ei\component\field\impl\string\cke\CkeEiField;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\component\field\impl\string\cke\model\CkeCssConfig;
use n2n\util\StringUtils;

class CkeHtmlBuilder {
	const CLASS_NAME_CKE = 'rocket-wysiwyg';
	const CLASS_NAME_CKE_INLINE = 'rocket-preview-inpage-wysiwyg';
	
	const ATTRIBUTE_TOOLBAR = 'data-toolbar';
	const ATTRIBUTE_BBCODE = 'data-bbcode';
	const ATTRIBUTE_TABLE_EDITING = 'data-table-editing';
	const ATTRIBUTE_BODY_ID = 'data-body-id';
	const ATTRIBUTE_BODY_CLASS = 'data-body-class';
	const ATTRIBUTE_CONTENTS_CSS = 'data-contents-css';
	const ATTRIBUTE_ADDITIONAL_STYLES = 'data-additional-styles';
	const ATTRIBUTE_FORMAT_TAGS = 'data-format-tags';
	const ATTRIBUTE_LINK_CONFIGURATIONS = 'data-link-configurations';
	
	private $html;
	private $view;
	
	public function __construct(HtmlView $view) {
		$this->view = $view;
		$this->html = $view->getHtmlBuilder();
	}
		
	public function getOut($contentsHtml) {
		return new Raw($contentsHtml);
	}
	
	public function out($contentsHtml) {
		return $this->view->out($this->getOut($contentsHtml));
	}
	

// 	public function wysiwygIframeBbcode($bbcode, CkeCssConfig $cssConfiguration = null) {
// 		echo $this->getWysiwygIframeBbcode($bbcode, $cssConfiguration);
// 	}
	
// 	public function getWysiwygIframeBbcode($bbcode, CkeCssConfig $cssConfiguration = null) {
// 		$bbcodeParser = new Parser();
// 		$bbcodeParser->addCodeDefinitionSet(new PhpbbDefinitionSet());
// 		$bbcodeParser->parse(htmlspecialchars($bbcode));
// 		$html = $this->getWysiwygContent($bbcodeParser->getAsHTML());
// 		return $this->getWysiwygIframeHtml($html, $cssConfiguration);
// 	}
	
// 	public function getWysiwygContent($contentsHtml) {
// 		$that = $this;
// 		return new Raw(preg_replace_callback('/href\s*=\s*"({.*?})"/', function($matches) use ($that) {
// 			$data = json_decode(html_entity_decode($matches[1]), true);
// 			if (null === $data
// 					|| !isset($data[self::DYNAMIC_LINK_CONFIG_REF])
// 					|| !isset($data[self::DYNAMIC_LINK_CONFIG_CHARACTERISTICS])) return $matches[0];
// 					try {
// 						$dynamicLinkBuilder = $that->view->lookup($data[self::DYNAMIC_LINK_CONFIG_REF]);
// 						if (!($dynamicLinkBuilder instanceof DynamicUrlBuilder)) return $matches[0];
	
// 						return 'href="' . $dynamicLinkBuilder->buildUrl($this->view->getHttpContext(),
// 								$data[self::DYNAMIC_LINK_CONFIG_CHARACTERISTICS]) . '"';
// 					} catch (LookupFailedException $e) {
// 						return $matches[0];
// 					}
// 		}, $contentsHtml));
// 	}
	
	public function iframe($contentsHtml, $bbcode = false, CkeCssConfig $cssConfiguration = null) {
		echo $this->getIframe($contentsHtml, $bbcode, $cssConfiguration);
	}
	
	public function getIframe($contentsHtml, CkeCssConfig $cssConfig = null) {
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
				. $this->getOut($contentsHtml) . '</div><iframe scrolling="auto" ' . $bodyIdHtml . ' class="rocket-wysiwyg-detail" ' . $bodyClassHtml
				. ' ' . self::ATTRIBUTE_CONTENTS_CSS . '="' . $headLinkHtml . '"></iframe>');
	}
	
	public function editor($propertyPath = null, $mode = self::MODE_NORMAL, $isBbCode = false,
			$isInline = false, $tableEditing = false, array $linkConfigurations = null,
			CkeCssConfig $cssConfiguration = null, array $attrs = null) {
		$this->view->out($this->getWysiwygEditor($propertyPath, $mode, $isBbCode, $isInline, $tableEditing, $linkConfigurations, $cssConfiguration, $attrs));
	}
	
	public function getEditor($propertyPath = null, $mode = self::MODE_NORMAL, $isBbCode = false,
			$inline = false, $tableEditing = false, array $linkConfigurations = null,
			CkeCssConfig $cssConfig = null, array $attrs = null) {
	
		$this->html->meta()->addLibrary(new CkeLibrary());
		$ckeClassName = $inline ? self::CLASS_NAME_CKE_INLINE : self::CLASS_NAME_CKE;

		$attrs = HtmlUtils::mergeAttrs((array) $attrs, array('class' => $ckeClassName, self::ATTRIBUTE_TOOLBAR => $mode,
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

		if (!empty($linkConfigurations)) {
			$attrs[self::ATTRIBUTE_LINK_CONFIGURATIONS] = StringUtils::jsonEncode(
					$this->prepareLinkConfigurations($linkConfigurations, $n2nLocale));
		}
		
		$this->html->meta()->addJs('js/wysiwyg.js', 'rocket', true);
		return $this->view->getFormHtmlBuilder()->getTextarea($propertyPath, $attrs);
	}
	
}

// class Cke {
// 	/**
// 	 * @return \rocket\spec\ei\component\field\impl\string\cke\ui\CkeComposer
// 	 */
// 	public static function simple() {
// 		return new CkeComposer(CkeEiField::MODE_SIMPLE);
// 	}

// 	/**
// 	 * @return \rocket\spec\ei\component\field\impl\string\cke\ui\CkeComposer
// 	 */
// 	public static function normal() {
// 		return new CkeComposer(CkeEiField::MODE_NORMAL);
// 	}

// 	/**
// 	 * @return \rocket\spec\ei\component\field\impl\string\cke\ui\CkeComposer
// 	 */
// 	public static function advanced() {
// 		return new CkeComposer(CkeEiField::MODE_ADVANCED);
// 	}
// }

// class CkeComposer {
// 	private $mode;
// 	private $useBbCode;
// 	private $ckeCssConfig;
// 	private $ckeLinkProviders = array();
	
// 	public function __construct(string $mode) {
// 		ArgUtils::valEnum($mode, CkeEiField::getModes());
// 		$this->mode = $mode;
// 	}
	
// 	public function bb($useBbCode = false) {
// 		$this->useBbCode = $useBbCode;
// 	}
	
// 	public function css(CkeCssConfig $ckeCssConfig) {
// 		$this->ckeCssConfig = $ckeCssConfig;
// 	}
	
// 	public function link(CkeLinkProvider ...$ckeLinkProviders) {
// 		$this->ckeLinkProviders = array_merge($this->ckeLinkProviders, $ckeLinkProviders);
// 	}
// }
