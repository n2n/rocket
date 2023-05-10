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
namespace rocket\impl\ei\component\prop\relation\conf;

use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\type\attrs\DataSet;
use n2n\util\type\attrs\LenientAttributeReader;
use rocket\impl\ei\component\prop\relation\model\RelationVetoableActionListener;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\util\spec\EiuEngine;
use n2n\persistence\meta\structure\Column;
use rocket\op\ei\util\Eiu;
use n2n\util\col\ArrayUtils;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\prop\relation\command\TargetReadEiCommandNature;
use rocket\op\ei\EiCmdPath;
use n2n\l10n\Lstr;
use rocket\impl\ei\component\prop\relation\command\TargetEditEiCommandNature;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;

class RelationConfig extends PropConfigAdaption {
	const ATTR_TARGET_EXTENSION_ID_KEY = 'targetExtension';
	const ATTR_MIN_KEY = 'min';	// tm
	const ATTR_MAX_KEY = 'max'; // tm
	const ATTR_REMOVABLE_KEY = 'replaceable'; // eto
	const ATTR_REDUCED_KEY = 'reduced'; // emb
	const ATTR_TARGET_REMOVAL_STRATEGY_KEY = 'targetRemovalStrategy';
	const ATTR_TARGET_ORDER_EI_PROP_PATH_KEY = 'targetOrderField'; // etm
	const ATTR_ORPHANS_ALLOWED_KEY = 'orphansAllowed';
	const ATTR_FILTERED_KEY = 'filtered';
	const ATTR_HIDDEN_IF_TARGET_EMPTY_KEY = 'hiddenIfTargetEmpty';
	const ATTR_MAX_PICKS_NUM_KEY = 'maxPicksNum'; // select
	
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	private $displayInOverViewDefault = true;
	
	function __construct(RelationModel $relationModel) {
		$this->relationModel = $relationModel;
	}
	

}
