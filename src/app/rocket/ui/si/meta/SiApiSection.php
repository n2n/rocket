<?php

namespace rocket\ui\si\meta;

enum SiApiSection: string {
	case CONTROL = 'execcontrol';
	case FIELD = 'callfield';
	case GET = 'get';
	case VAL = 'val';
	case SORT = 'sort';
}
