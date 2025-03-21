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

namespace rocket\impl\ei\component\prop\meta;


use rocket\ui\si\content\impl\meta\SiCrumbGroup;
use rocket\ui\si\content\impl\meta\SiCrumb;
use n2n\util\StringUtils;

class SiCrumbGroupFactory {
	static function parseCrumbGroups(array $patterns) {
		$crumbGroups = [];

		foreach ($patterns as $pattern) {
			$crumbGroups[] = new SiCrumbGroup(self::parseCrumbs($pattern));
		}

		return $crumbGroups;
	}

	/**
	 * @param string $pattern
	 * @return SiCrumb[]
	 */
	private static function parseCrumbs(string $pattern) {
		$crumbs = [];

		$curStr = '';
		$bracketOpen = false;
		foreach (preg_split('//u', $pattern, -1, PREG_SPLIT_NO_EMPTY) as $char) {
			if ($char === '}' && $bracketOpen) {
				self::addIcon($crumbs, $curStr . $char);
				$curStr = '';
				$bracketOpen = false;
				continue;
			}

			if ($char === '{' && !$bracketOpen) {
				self::addLabel($crumbs, $curStr);
				$curStr = '';
				$bracketOpen = true;
			}

			$curStr .= $char;
		}

		self::addLabel($crumbs, $curStr);

		return $crumbs;
	}

	private static function addLabel(&$crumbs, $str) {
		if (mb_strlen($str) === 0) {
			return;
		}

		$crumbs[] = SiCrumb::createLabel($str);
	}

	const BRACKETED_ICON_PREFIX = 'icon:';

	private static function addIcon(&$crumbs, $str) {
		if (mb_strlen($str) === 0) {
			return;
		}

		if (!StringUtils::startsWith('{' . self::BRACKETED_ICON_PREFIX, $str)) {
			$crumbs[] = SiCrumb::createLabel($str);
			return;
		}

		$crumbs[] = SiCrumb::createIcon(trim(mb_substr($str, mb_strlen('{' . self::BRACKETED_ICON_PREFIX), -1)));
	}
}
