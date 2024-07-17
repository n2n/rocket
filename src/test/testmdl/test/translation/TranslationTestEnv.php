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

namespace testmdl\test\translation;

use n2n\test\TestEnv;
use n2n\l10n\N2nLocale;
use testmdl\bo\TranslatableTestObj;
use testmdl\bo\TranslationTestObj;
use n2n\util\HashUtils;

enum TranslationTestEnv {

	static function setUpTranslatableTestObj(): TranslatableTestObj {
		$obj = new TranslatableTestObj();

		TestEnv::em()->persist($obj);

		return $obj;
	}

	static function findTranslatableTestObj(int $id): ?TranslatableTestObj {
		return TestEnv::em()->find(TranslatableTestObj::class, $id);
	}

	static function setUpTranslationTestObj(TranslatableTestObj $translationContainerTestObj,
			N2nLocale|string $n2nLocale, string $name = null): TranslationTestObj {
		$obj = new TranslationTestObj();
		$obj->setN2nLocale(N2nLocale::create($n2nLocale));
		$obj->name = $name ?? HashUtils::base36Uniqid();
		$translationContainerTestObj->addTranslationTestObj($obj);

		TestEnv::em()->persist($obj);

		return $obj;
	}

	static function findTranslationTestObj(int $id): ?TranslationTestObj {
		return TestEnv::em()->find(TranslationTestObj::class, $id);
	}

}
