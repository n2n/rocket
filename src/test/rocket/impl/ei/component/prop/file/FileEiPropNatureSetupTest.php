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

namespace rocket\impl\ei\component\prop\file;

use PHPUnit\Framework\TestCase;
use rocket\test\SpecTestEnv;
use rocket\test\GeneralTestEnv;
use testmdl\string\bo\FileTestObj;
use rocket\op\ei\UnknownEiTypeException;
use rocket\ui\gui\field\impl\file\ImageDimensionsImportMode;
use n2n\io\managed\img\ImageDimension;
use n2n\io\img\impl\ImageSourceFactory;

class FileEiPropNatureSetupTest extends TestCase {


	function setUp(): void {
		GeneralTestEnv::teardown();
	}

	/**
	 * @throws UnknownEiTypeException
	 */
	function testSetup(): void {
		$spec = SpecTestEnv::setUpSpec([FileTestObj::class]);
		$eiType = $spec->getEiTypeByClassName(FileTestObj::class);

		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();
		$this->assertCount(3, $eiProps);

		$nature = $eiProps['file']->getNature();
		$this->assertInstanceOf(FileEiPropNature::class, $nature);
		$this->assertNull($nature->maxSize);
		$this->assertNull($nature->allowedExtensions);
		$this->assertNull($nature->allowedMimeTypes);
		$this->assertSame(ImageDimensionsImportMode::ALL, $nature->dimensionImportMode);
		$this->assertEmpty($nature->extraThumbDimensions);
		$this->assertTrue($nature->imageRecognized);

		$nature = $eiProps['annoatedFile']->getNature();
		$this->assertInstanceOf(FileEiPropNature::class, $nature);
		$this->assertSame(1024, $nature->maxSize);
		$this->assertSame(['pdf', 'ods'], $nature->allowedExtensions);
		$this->assertSame(['holeradio/huii'], $nature->allowedMimeTypes);
		$this->assertSame(ImageDimensionsImportMode::NONE, $nature->dimensionImportMode);
		$this->assertEquals([new ImageDimension(230, 23, false, false)], $nature->extraThumbDimensions);
		$this->assertFalse($nature->imageRecognized);

		$nature = $eiProps['annoatedImageFile']->getNature();
		$this->assertInstanceOf(FileEiPropNature::class, $nature);
		$this->assertSame(2048, $nature->maxSize);
		$this->assertSame(ImageSourceFactory::getSupportedExtensions(), $nature->allowedExtensions);
		$this->assertSame(ImageSourceFactory::getSupportedMimeTypes(), $nature->allowedMimeTypes);
		$this->assertSame(ImageDimensionsImportMode::USED_ONLY, $nature->dimensionImportMode);
		$this->assertEquals([new ImageDimension(240, 24, false, false)], $nature->extraThumbDimensions);
		$this->assertTrue($nature->imageRecognized);
	}

}