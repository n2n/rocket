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
namespace rocket\impl\ei\component\prop\string\cke\ui;

use n2n\util\StringUtils;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\Raw;
use n2n\l10n\N2nLocale;
use n2n\util\uri\Url;
use rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig;
use rocket\impl\ei\component\prop\string\cke\model\CkeUtils;
use n2n\util\uri\UnavailableUrlException;
use n2n\reflection\CastUtils;
use rocket\impl\ei\component\prop\string\cke\model\CkeStyle;
use lib\rocket\impl\ei\component\prop\string\cke\model\CkeBbcodeParser;

class CkeHtmlBuilder {

	private $html;
	private $view;

	private $linkProviders = array();

	public function __construct(HtmlView $view) {
		$this->view = $view;
		$this->html = $view->getHtmlBuilder();
	}

	public function out(string $contentsHtml, N2nLocale $n2nLocale = null) {
		return $this->view->out($this->getOut($contentsHtml, $n2nLocale));
	}

	public function getOut(string $contentsHtml, N2nLocale $n2nLocale = null) {
		$that = $this;
		$n2nLocale = $n2nLocale ?? $this->view->getN2nLocale();

		return new Raw(preg_replace_callback('/(href\s*=\s*")?\s*(ckelink:\?provider=[^"<]+&amp;key=[^"<]+)(")?/',
				function($matches) use ($that, $n2nLocale) {
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
					} catch (UnavailableUrlException $e) {
						return '';
					}

					if ($url === null) {
						$url = $query['key'];
					}

					return $matches[1] .  $url . ($matches[3] ?? '');
				}, $contentsHtml));
	}
	
	public function editor($propertyPath = null, CkeComposer $ckeComposer = null) {
		$this->view->out($this->getEditor($propertyPath, $ckeComposer));
	}

	public function getEditor($propertyPath = null, CkeComposer $ckeComposer = null, CkeCssConfig $ckeCssConfig = null, 
			array $linkProviders = array()) {
		$this->html->meta()->addLibrary(new CkeLibrary());

		$ckeConfig = null;
		if ($ckeComposer !== null) {
			$ckeConfig = $ckeComposer->toCkeConfig();
		} else {
			$ckeConfig = CkeConfig::createDefault();
		}

		$attrs = array('mode' => $ckeConfig->getMode(),
			'tableEditing' => $ckeConfig->isTablesEnabled(),
			'bbcode' => $ckeConfig->isBbcodeEnabled(),
			'additionalStyles' => null,
			'bodyClass' => null,
			'bodyId' => null,
			'contentsCss' => null);

		if ($ckeCssConfig !== null) {
			$attrs['bodyId'] = $ckeCssConfig->getBodyId();
			$attrs['bodyClass'] = $ckeCssConfig->getBodyClass();
			$attrs['contentsCss'] = $this->getCssPaths($ckeCssConfig);
			$attrs['additionalStyles'] = $this->prepareAdditionalStyles($ckeCssConfig->getAdditionalStyles());
			$attrs['formatTags'] = implode(';', (array) $ckeCssConfig->getFormatTags());
		}

		$attrs = array('class' => 'rocket-impl-cke-classic', 'data-rocket-impl-toolbar' => json_encode($attrs),
				'data-link-configurations' => json_encode($this->buildLinkConfigData($linkProviders)));
		return $this->view->getFormHtmlBuilder()->getTextarea($propertyPath, $attrs);
	}

	public function getIframe(string $contentsHtml, CkeCssConfig $ckeCssConfig = null, array $linkProviders = null) {
		$this->linkProviders = $linkProviders;
		$headLinkHtml = '';
		$bodyIdHtml = '';
		$bodyClassHtml = '';
		
		if ($ckeCssConfig) {
			$headLinkHtml = str_replace('"', '\'',
					StringUtils::jsonEncode((array) $this->getCssPaths($ckeCssConfig)));
			$bodyIdHtml = ($bodyId = $ckeCssConfig->getBodyId()) ?  'data-body-id="' . $bodyId . '"' : '';
			$bodyClassHtml = ($bodyClass = $ckeCssConfig->getBodyClass()) ? ' ' . 'data-body-class="'
					. $bodyClass . '"' : '';
		}

		$contentsHtml = htmlspecialchars(str_replace('"', "'", $this->getOut($contentsHtml, $this->view->getN2nLocale())));
		$this->html->meta()->addJs('impl/js/cke.js', 'rocket', true);
		return new Raw('<iframe scrolling="auto" ' . $bodyIdHtml
			. ' class="rocket-cke-detail" ' . $bodyClassHtml
			. 'data-contents-css="' . $headLinkHtml . '" data-content-html-json="'
			. $contentsHtml . '"></iframe>');
	}

	private function getCssPaths(CkeCssConfig $cssConfig) {
		if (empty($cssPaths = $cssConfig->getContentCssUrls($this->view))) {
			return array();
		}

		$tmpCssPaths = array();
		foreach ($cssPaths as $cssPath) {
			$tmpCssPaths[] = (string) $cssPath;
		}
		return $tmpCssPaths;
	}

	private function buildLinkConfigData(array $ckeLinkProviders, N2nLocale $linkN2nLocale = null) {
		$linkN2nLocale = (null !== $linkN2nLocale) ? $linkN2nLocale : $this->view->getN2nLocale();
		$linkConfigData = array();
		foreach ($ckeLinkProviders as $providerName => $ckeLinkProvider) {
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

	private function lookupLinkProvider(string $lookupId) {
		if (array_key_exists($lookupId, $this->linkProviders)) {
			return $this->linkProviders[$lookupId];
		}

		try {
			return $this->linkProviders[$lookupId] = CkeUtils::lookupCkeLinkProvider($lookupId, $this->view->getN2nContext());
		} catch (\InvalidArgumentException $e) {
			return $this->linkProviders[$lookupId] = null;
		}
	}

	private function prepareAdditionalStyles($additionalStyles) {
		$encodable = array();
		foreach ((array) $additionalStyles as $style) {
			CastUtils::assertTrue($style instanceof CkeStyle);
			$encodable[] = $style->getValueForJsonEncode();
		}
		return $encodable;
	}
}