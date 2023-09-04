<?php

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
