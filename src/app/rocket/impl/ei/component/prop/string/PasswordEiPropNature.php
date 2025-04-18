<?php
///*
// * Copyright (c) 2012-2016, Hofmänner New Media.
// * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
// *
// * This file is part of the n2n module ROCKET.
// *
// * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
// * GNU Lesser General Public License as published by the Free Software Foundation, either
// * version 2.1 of the License, or (at your option) any later version.
// *
// * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
// * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
// *
// * The following people participated in this project:
// *
// * Andreas von Burg...........:	Architect, Lead Developer, Concept
// * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
// * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
// */
//namespace rocket\impl\ei\component\prop\string;
//
//use n2n\util\crypt\hash\HashUtils;
//use n2n\util\crypt\hash\algorithm\BlowfishAlgorithm;
//use n2n\util\crypt\hash\algorithm\Sha256Algorithm;
//use n2n\util\ex\IllegalStateException;
//use rocket\op\ei\util\Eiu;
//use rocket\impl\ei\component\prop\string\conf\PasswordConfig;
//use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropNatureAdapter;
//use rocket\op\ei\util\factory\EifGuiField;
//use rocket\si\content\impl\SiFields;
//use rocket\impl\ei\component\prop\string\conf\AlphanumericConfig;
//use n2n\config\InvalidConfigurationException;
//
//class PasswordEiPropNature extends DraftablePropertyEiPropNatureAdapter {
//	private $passwordConfig;
//	private $alphanumericConfig;
//
//	function __construct() {
//		parent::__construct();
//		$this->passwordConfig = new PasswordConfig();
//		$this->alphanumericConfig = new AlphanumericConfig();
//	}
//
//	public function prepare() {
//		$this->getConfigurator()->addAdaption($this->passwordConfig)
//				->addAdaption($this->alphanumericConfig);
//	}
//
//	public function buildOutGuiField(Eiu $eiu): ?BackableGuiField  {
//		return $eiu->factory()->newGuiField(SiFields::stringOut(null)
//					->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs()));
//	}
//
//	public function buildInGuiField(Eiu $eiu): ?BackableGuiField {
//
//		$siField = SiFields::passwordIn()
//				->setMandatory($this->isMandatory())
//				->setPasswordSet(!empty($eiu->field()->getValue()))
//				->setMinlength($this->alphanumericConfig->getMinlength())
//				->setMaxlength($this->alphanumericConfig->getMaxlength())
//				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
//
//		return $eiu->factory()->newGuiField($siField)
//				->setSaver(function () use ($siField, $eiu) {
//			if (!empty($siField->getRawPassword())) {
//				$eiu->field()->setValue($this->buildPasswordHash($siField->getRawPassword()));
//			}
//		});
//	}
//
//	private function buildPasswordHash(string $rawPassword) {
//		return password_hash($rawPassword, PASSWORD_DEFAULT);
//	}
//}