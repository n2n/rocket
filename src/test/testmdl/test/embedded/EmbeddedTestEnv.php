<?php

namespace testmdl\test\embedded;

use n2n\test\TestEnv;
use testmdl\embedded\bo\EmbeddingContainerTestObj;

enum EmbeddedTestEnv {

	static function setEmbeddingContainerTestObj(): EmbeddingContainerTestObj {
		$obj = new EmbeddingContainerTestObj();

		TestEnv::em()->persist($obj);

		return $obj;
	}

	static function findEmbeddingContainerTestObj(int $id): ?EmbeddingContainerTestObj {
		return TestEnv::em()->find(EmbeddingContainerTestObj::class, $id);
	}

}
