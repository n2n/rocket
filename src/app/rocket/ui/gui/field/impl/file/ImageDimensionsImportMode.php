<?php

namespace rocket\ui\gui\field\impl\file;

enum ImageDimensionsImportMode: string {

	case ALL = 'all';
	case USED_ONLY = 'usedOnly';
	case NONE = 'none';
}
