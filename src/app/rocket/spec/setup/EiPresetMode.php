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
namespace rocket\spec\setup;

use rocket\attribute\EiPreset;

/**
 * Used by {@link EiPreset} attribute define a general approach to initialize an EiType.
 */
enum EiPresetMode {
	/**
	 * Same as READ_COMMANDS and READ_FIELDS together
	 */
	case READ;
	/**
	 * Same as EDIT_COMMANDS and EDIT_FIELDS together
	 */
	case EDIT;
	/**
	 * Adds suitable EiCommands (if found) which provide read only functionality.
	 */
	case READ_CMDS;
	/**
	 * Adds suitable EiCommands (if found) which provide read or edit functionality .
	 */
	case EDIT_CMDS;
	/**
	 * Adds for every property a suitable EiProp (if found) which is read only.
	 */
	case READ_PROPS;
	/**
	 * Adds for every property a suitable EiProp (if found)  which is editable.
	 */
	case EDIT_PROPS;
	/**
	 *
	 */
	case READ_PROPS_EDIT_CMDS;

	function isReadPropsMode(): bool {
		return match ($this) {
			self::READ, self::READ_PROPS, self::READ_PROPS_EDIT_CMDS => true,
			default => false,
		};
	}

	function isEditPropsMode(): bool {
		return match ($this) {
			self::EDIT, self::EDIT_PROPS => true,
			default => false,
		};
	}

	function isReadCmdsMode(): bool {
		return match ($this) {
			self::READ, self::READ_CMDS, self::EDIT_CMDS, self::READ_PROPS_EDIT_CMDS  => true,
			default => false
		};
	}

	function isEditCmdsMode(): bool {
		return match ($this) {
			self::EDIT, self::EDIT_CMDS, self::READ_PROPS_EDIT_CMDS => true,
			default => false,
		};
	}
}
