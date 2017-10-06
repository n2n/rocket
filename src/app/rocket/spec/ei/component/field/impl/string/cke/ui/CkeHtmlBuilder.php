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
use rocket\spec\ei\component\field\impl\string\cke\model\CkeCssConfig;
use n2n\l10n\N2nLocale;
use n2n\util\uri\Url;
use n2n\reflection\ArgUtils;
use n2n\web\http\nav\UnavailableLinkException;
use rocket\spec\ei\component\field\impl\string\cke\CkeEiProp;

class CkeHtmlBuilder {
	const CLASS_NAME_CKE = 'rocket-cke-classic';
	const CLASS_NAME_CKE_INLINE = 'rocket-cke-inline';
	
	private $html;
	private $view;
	
	public function __construct(HtmlView $view) {
		$this->view = $view;
		$this->html = $view->getHtmlBuilder();
	}
		
	
	public function out($contentsHtml, N2nLocale $n2nLocale = null) {
		return $this->view->out($this->getOut($contentsHtml, $n2nLocale));
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
	
	public function editor($propertyPath = null, CkeComposer $ckeComposer = null) {
		$this->view->out($this->getEditor($propertyPath, $ckeComposer));
	}
	
	public function getEditor($propertyPath = null, CkeComposer $ckeComposer = null) {
		$this->html->meta()->addLibrary(new CkeLibrary());
		
		$ckeConfig = null;
		if ($ckeComposer !== null) {
			$ckeConfig = $ckeComposer->toCkeConfig();
		} else {
			$ckeConfig = CkeConfig::createDefault();
		}
		
	
		$attrs = array('class' => 'rocket-impl-cke-classic');
		$toolbar = null;
		switch ($ckeConfig->getMode()) {
			case CkeEiProp::MODE_SIMPLE:
				$attrs['data-rocket-impl-toolbar'] = json_encode(['headings', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote']);
				break;
			case CkeEiProp::MODE_NORMAL:
				$attrs['data-rocket-impl-toolbar'] = json_encode(['headings', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote']);
				break;
			case CkeEiProp::MODE_ADVANCED:
				$attrs['data-rocket-impl-toolbar'] = json_encode(['headings', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote']);
				break;
		}
		
// 		this.simpleToolbar =  [
// 		{ name: "basicstyles", items : [ "Bold", "Italic", "Underline", "Strike", "RemoveFormat" ]}
// 		];
		
// 		this.normalToolbar = [
// 		{ name: 'document', items: [ ]},
// 		{ name: "basicstyles", items : [ "Bold", "Italic", "Underline", "Strike", "RemoveFormat" ]},
// 		{ name: "clipboard", items : [ "Cut", "Copy", "Paste", "PateText", "PasteFromWord", "Undo", "Redo" ] },
// 		{ name: "editing", items: [ ]},
// 		{ name: "basicstyles", items : [ "Subscript", "Superscript" ]},
// 		{ name: "paragraph", items : [ "NumberedList", "BulletedList", "Outdent", "Indent", "Blockquote", "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock" ] },
// 		{ name: "links", items: [ "Link", "Unlink", "Anchor"]},
// 		{ name: "insert", items: [ "HorizontalRule", "SpecialChar"]},
// 		{ name: "styles", items: [ "Styles", "Format"]},
// 		{ name: "tools", items: [ "Maximize" ]},
// 		{ name: "about", items: [ "About" ]}
// 		];
		
		
		return $this->view->getFormHtmlBuilder()->getTextarea($propertyPath, $attrs);
	}
}

class Cke {
	/**
	 * @return CkeComposer
	 */
	public static function classic() {
		return new CkeComposer();
	}
}

class CkeComposer {
	private $mode = CkeEiProp::MODE_NORMAL;
	private $tableEditing;
	private $table;
	
	public function __construct() {
	}

	/**
	 * @param string $mode
	 * @return \rocket\spec\ei\component\field\impl\string\cke\ui\CkeComposer
	 */
	public function mode(string $mode) {
		ArgUtils::valEnum($mode, CkeEiProp::getModes());
		$this->mode = $mode;
		return $this;
	}
	
	/**
	 * @param bool $table
	 * @return \rocket\spec\ei\component\field\impl\string\cke\ui\CkeComposer
	 */
	public function table(bool $table) {
		$this->table = $table;
		return $this;
	}
	
	/**
	 * @return \rocket\spec\ei\component\field\impl\string\cke\ui\CkeConfig
	 */
	public function toCkeConfig() {
		return new CkeConfig($this->mode, $this->table);
	}
}

class CkeConfig {
	private $mode;
	private $tableEnabled;
	
	public function __construct(string $mode, bool $tablesEnabled) {
		$this->mode = $mode;
		$this->tableEnabled = $tablesEnabled;
	}
	
	public function getMode() {
		return $this->mode;
	}
	
	public function isTablesEnabled() {
		return $this->tableEnabled;
	}
	
	public static function createDefault() {
		return new CkeConfig(CkeEiProp::MODE_NORMAL, false);
	}
}