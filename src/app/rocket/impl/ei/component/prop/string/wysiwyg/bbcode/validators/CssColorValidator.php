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
namespace rocket\impl\ei\component\prop\string\wysiwyg\bbcode\validators;

/**
 * An InputValidator for CSS color values. This is a very rudimentary
 * validator. It will allow a lot of color values that are invalid. However,
 * it shouldn't allow any invalid color values that are also a security
 * concern.
 *
 * @author jbowens
 * @since May 2013
 */
use rocket\impl\ei\component\prop\string\wysiwyg\bbcode\InputValidator;

class CssColorValidator implements InputValidator
{

	/**
	 * Returns true if $input uses only valid CSS color value
	 * characters.
	 *
	 * @param $input  the string to validate
	 */
	public function validate($input)
	{
		return (bool) preg_match('/^[A-z0-9\-#., ()%]+$/', $input);
	}

}
