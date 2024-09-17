<?php

namespace rocket\ui\si\content\impl;

use n2n\io\managed\File;
use n2n\core\container\N2nContext;

interface SiFileFactory {

	function createSiFile(File $file, N2nContext $n2NContext): SiFile;
}