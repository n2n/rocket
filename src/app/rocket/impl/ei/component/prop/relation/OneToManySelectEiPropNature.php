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
namespace rocket\impl\ei\component\prop\relation;

use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\util\type\ArgUtils;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\impl\ei\component\prop\relation\model\ToManyEiField;
use rocket\impl\ei\component\prop\relation\model\gui\ToManyGuiFieldFactory;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\ui\gui\field\BackableGuiField;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use n2n\util\ex\UnsupportedOperationException;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\ui\gui\ViewMode;


class OneToManySelectEiPropNature extends RelationEiPropNatureAdapter {
	
	public function __construct(RelationEntityProperty $entityProperty, PropertyAccessProxy $accessProxy) {
		ArgUtils::assertTrue($entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_MANY);

		parent::__construct($entityProperty, $accessProxy,
				new RelationModel($this, false, true, RelationModel::MODE_SELECT));

		$this->displayConfig = new DisplayConfig(ViewMode::read());
		$this->editConfig = new EditConfig(true, false, true, false);
	}
	
	function buildEiField(Eiu $eiu): ?EiFieldNature {
		$targetEiuFrame = $eiu->frame()->forkSelect($eiu->prop(), $eiu->object())->frame()
				->exec($this->getRelationModel()->getTargetReadEiCmdPath());
		
		return new ToManyEiField($eiu, $targetEiuFrame, $this, $this->getRelationModel());
	}
	
//	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
//		if ($readOnly || $this->relationModel->isReadOnly()) {
//			return new RelationLinkGuiField($eiu, $this->getRelationModel());
//		}
//
//		return new ToManyGuiFieldFactory($eiu, $this->getRelationModel());
//	}

	function buildInGuiField(Eiu $eiu): ?BackableGuiField {
		throw new UnsupportedOperationException('OneToManySelectEiPropNature is always read-only.');
//		return (new ToManyGuiFieldFactory($this->getRelationModel()))->createInGuiField($eiu);
	}

	function buildOutGuiField(Eiu $eiu): ?BackableGuiField {
		return (new ToManyGuiFieldFactory($this->getRelationModel()))->createOutGuiField($eiu);
	}
}
