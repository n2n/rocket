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
use rocket\spec\ei\component\field\impl\string\cke\model\CkeCssConfig;
use n2n\util\StringUtils;
use n2n\l10n\N2nLocale;
use n2n\util\uri\Url;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\component\field\impl\string\cke\model\CkeUtils;
use n2n\web\http\nav\UnavailableLinkException;

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
		
	
	public function out($contentsHtml, N2nLocale $n2nLocale = null) {
		return $this->view->out($this->getOut($contentsHtml, $n2nLocale));
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
	
	private $linkProviders = array();
	
	private function lookupLinkProvider($lookupId) {
		if (array_key_exists($lookupId, $this->linkProviders)) {
			return $this->linkProviders[$lookupId];
		}
		
		try {
			return $this->linkProviders[$lookupId] = CkeUtils::lookupCkeLinkProvider($lookupId, $this->view->getN2nContext());
		} catch (\InvalidArgumentException $e) {
			return $this->linkProviders[$lookupId] = null;
		}
	}
	
	public function getOut($contentsHtml, N2nLocale $n2nLocale = null) {
		$that = $this;
		$n2nLocale = $n2nLocale ?? $this->view->getN2nLocale();
		return new Raw(preg_replace_callback('/(href\s*=\s*")?\s*(ckelink:\?provider=[^"<]+&amp;key=[^"<]+)(")?/', function($matches) use ($that, $n2nLocale) {
			$url = null;
			try {
				$url = Url::create(htmlspecialchars_decode($matches[2]), true);
			} catch (\InvalidArgumentException $e) {
				return '';
			}
			
			$query = $url->getQuery()->toArray();
			$ckeLinkProvider = null;
			if (!isset($query['provider']) || !isset($query['key'])) {
				return '';
			}
			
			$ckeLinkProvider = $that->lookupLinkProvider($query['provider']);
			if ($ckeLinkProvider === null) {
				return '';
			}
			
			try {
				$url = $ckeLinkProvider->buildUrl($query['key'], $that->view, $n2nLocale);
			} catch (UnavailableLinkException $e) {
				return '';
			}
			
			if ($url === null) {
				$url = $query['key'];
			} 
			
			return $matches[1] .  $url . ($matches[3] ?? '');
		}, $contentsHtml));
	}
	
	public function iframe($contentsHtml, $bbcode = false, CkeCssConfig $cssConfiguration = null) {
		echo $this->getIframe($contentsHtml, $bbcode, $cssConfiguration);
	}
	
	public function getIframe($contentsHtml, $ckeCssConfig = null) {
		$ckeCssConfig = $this->lookupCkeCssConfig($ckeCssConfig);
		// 		$this->htmlBuilder->addLibrary(new JQueryLibrary());
	
		$headLinkHtml = '';
		$bodyIdHtml = '';
		$bodyClassHtml = '';
		if ($ckeCssConfig) {
			$headLinkHtml = str_replace('"', '\'', StringUtils::jsonEncode((array) $this->getCssPaths($ckeCssConfig)));
			$bodyIdHtml = ($bodyId = $ckeCssConfig->getBodyId()) ?  self::ATTRIBUTE_BODY_ID . '="' . $bodyId . '"' : '';
			$bodyClassHtml = ($bodyClass = $ckeCssConfig->getBodyClass()) ? ' ' . self::ATTRIBUTE_BODY_CLASS . '="' . $bodyClass . '"' : '';
		}

		$this->html->meta()->addJs('js/wysiwyg.js', 'rocket', true);
		return new Raw('<div class="rocket-wysiwyg-content" style="display:none">'
				. $this->getOut($contentsHtml) . '</div><iframe scrolling="auto" ' . $bodyIdHtml . ' class="rocket-wysiwyg-detail" ' . $bodyClassHtml
				. ' ' . self::ATTRIBUTE_CONTENTS_CSS . '="' . $headLinkHtml . '"></iframe>');
	}
	
	private function getCssPaths(CkeCssConfig $cssConfig) {
		if (empty($cssPaths = $cssConfig->getContentCssPaths($this->view))) {
			return array();
		}
	
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
	
	public function editor($propertyPath = null, $mode = self::MODE_NORMAL, $isBbCode = false,
			$isInline = false, $tableEditing = false, array $linkConfigurations = null,
			CkeCssConfig $cssConfiguration = null, array $attrs = null, N2nLocale $linkN2nLocale = null) {
		$this->view->out($this->getWysiwygEditor($propertyPath, $mode, $isBbCode, $isInline, $tableEditing, 
				$linkConfigurations, $cssConfiguration, $attrs, $linkN2nLocale));
	}
	
	public function getEditor($propertyPath = null, string $mode = self::MODE_NORMAL, bool $isBbCode = false,
			bool $inline = false, $tableEditing = false, array $ckeLinkProviderLookupIds = null,
			$ckeCssConfig = null, array $attrs = null, N2nLocale $linkN2nLocale = null) {
		
		$ckeCssConfig = $this->lookupCkeCssConfig($ckeCssConfig);
		
		ArgUtils::valArray($ckeLinkProviderLookupIds, 'string', 'ckeLinkProviderCkeLookupIds', true);
		$ckeLinkProviers = CkeUtils::lookupCkeLinkProviders($ckeLinkProviderLookupIds, $this->view->getN2nContext());
	
		$this->html->meta()->addLibrary(new CkeLibrary());
		$ckeClassName = $inline ? self::CLASS_NAME_CKE_INLINE : self::CLASS_NAME_CKE;

		$attrs = HtmlUtils::mergeAttrs((array) $attrs, array('class' => $ckeClassName, self::ATTRIBUTE_TOOLBAR => $mode,
				self::ATTRIBUTE_BBCODE => $isBbCode, self::ATTRIBUTE_TABLE_EDITING => $tableEditing));

		if ($ckeCssConfig !== null) {
			$attrs[self::ATTRIBUTE_BODY_CLASS] = $ckeCssConfig->getBodyClass();
			$attrs[self::ATTRIBUTE_BODY_ID] = $ckeCssConfig->getBodyId();
			if (!empty($cssPaths = $this->getCssPaths($ckeCssConfig))) {
				$attrs[self::ATTRIBUTE_CONTENTS_CSS] = str_replace('"', '\'', StringUtils::jsonEncode((array) $cssPaths));
			}
			$attrs[self::ATTRIBUTE_ADDITIONAL_STYLES] = StringUtils::jsonEncode($this->prepareAdditionalStyles($ckeCssConfig->getAdditionalStyles()));
			$attrs[self::ATTRIBUTE_FORMAT_TAGS] = implode(';', (array) $ckeCssConfig->getFormatTags());
		}

		if (!empty($ckeLinkProviers)) {
			$attrs[self::ATTRIBUTE_LINK_CONFIGURATIONS] = StringUtils::jsonEncode(
					$this->buildLinkConfigData($ckeLinkProviers, $linkN2nLocale));
		}
		
		$this->html->meta()->addJs('js/wysiwyg.js', 'rocket', true);
		return $this->view->getFormHtmlBuilder()->getTextarea($propertyPath, $attrs);
	}
	
	private function lookupCkeCssConfig($ckeCssConfig) {
		if ($ckeCssConfig !== null && !($ckeCssConfig instanceof CkeCssConfig)) {
			ArgUtils::valType($ckeCssConfig, array(CkeCssConfig::class, 'string'), true, 'ckeCssConfig');
			return CkeUtils::lookupCkeCssConfig($ckeCssConfig, $this->view->getN2nContext());
		}
		
		return null;
	}

	private function buildLinkConfigData($ckeLinkProviers, N2nLocale $linkN2nLocale = null) {
		$linkN2nLocale = (null !== $linkN2nLocale) ? $linkN2nLocale : $this->view->getN2nLocale();
		$linkConfigData = array();
		foreach ($ckeLinkProviers as $providerName => $ckeLinkProvider) {
			$title = $ckeLinkProvider->getTitle();
			$linkConfigData[$title] = array();
			$linkConfigData[$title]['items'] = array();
			$linkConfigData[$title]['open-in-new-window'] = $ckeLinkProvider->isOpenInNewWindow();
			foreach ($ckeLinkProvider->getLinkOptions($linkN2nLocale) as $key => $label) {
				$url = (new Url('ckelink'))->chQuery(array('provider' => $providerName, 'key' => $key));
				$linkConfigData[$title]['items'][] = array($label, (string) $url);
			}
		}
		return $linkConfigData;
	}
	
}

// class Cke {
// 	/**
// 	 * @return \rocket\spec\ei\component\field\impl\string\cke\ui\CkeComposer
// 	 */
// 	public static function simple() {
// 		return new CkeComposer(CkeEiProp::MODE_SIMPLE);
// 	}

// 	/**
// 	 * @return \rocket\spec\ei\component\field\impl\string\cke\ui\CkeComposer
// 	 */
// 	public static function normal() {
// 		return new CkeComposer(CkeEiProp::MODE_NORMAL);
// 	}

// 	/**
// 	 * @return \rocket\spec\ei\component\field\impl\string\cke\ui\CkeComposer
// 	 */
// 	public static function advanced() {
// 		return new CkeComposer(CkeEiProp::MODE_ADVANCED);
// 	}
// }

// class CkeComposer {
// 	private $mode;
// 	private $useBbCode;
// 	private $ckeCssConfig;
// 	private $ckeLinkProviders = array();
	
// 	public function __construct(string $mode) {
// 		ArgUtils::valEnum($mode, CkeEiProp::getModes());
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
