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
namespace rocket\spec\config\extr;

class RawDef {
	const SPECS_KEY = 'specs';
	const SPEC_TYPE_KEY = 'type';
	const SPEC_CUSTOM_CONTROLLER_CLASS_KEY = 'controller';
	
	const SPEC_EI_DATA_SOURCE_NAME_KEY = 'dataSourceName';
	const SPEC_EI_NESTED_SET_STRATEGY_KEY = 'nestedSetStrategy';
	const SPEC_EI_NESTED_SET_STRATEGY_LEFT_KEY = 'left';
	const SPEC_EI_NESTED_SET_STRATEGY_RIGHT_KEY = 'right';
	
	const SPEC_EI_CLASS_KEY = 'entity';
	const SPEC_EI_DEFAULT_MASK_ID = 'defaultMaskId';
	
	const COMMON_EI_MASKS_KEY = 'commonEiMasks';
	const COMMON_EI_MASK_DRAFTING_ALLOWED_KEY = 'draftingAllowed';
	
	const OVERVIEW_GUI_FIELD_ORDER_KEY = 'overviewOrder';
	const BULKY_GUI_FIELD_ORDER_KEY = 'bulkyOrder';
	const DETAIL_GUI_FIELD_ORDER_KEY = 'detailOrder';
	const EDIT_GUI_FIELD_ORDER_KEY = 'editOrder';
	const ADD_GUI_FIELD_ORDER_KEY = 'addOrder';
	
	const GUI_FIELD_ORDER_GROUP_TITLE_KEY = 'title';
	const GUI_FIELD_ORDER_GROUP_TYPE_KEY = 'type';
	const GUI_FIELD_ORDER_KEY = 'displayStructure';

	const EI_DEF_PARTIAL_CONTROL_ORDER_KEY = 'partialControlOrder';
	const EI_DEF_OVERALL_CONTROL_ORDER_KEY = 'overallControlOrder';
	const EI_DEF_ENTRY_CONTROL_ORDER_KEY = 'entryControlOrder';
		
	const EI_DEF_LABEL_KEY = 'label';
	const EI_DEF_PLURAL_LABEL_KEY = 'pluralLabel';
	const EI_DEF_DRAFTING_ALLOWED_KEY = 'draftingAllowed';
	const EI_DEF_REPRESENTATION_STRING_PATTERN_KEY = 'identityStringPattern';
	const EI_DEF_PREVIEW_CONTROLLER_LOOKUP_ID_KEY = 'previewControllerLookupId';
	const EI_DEF_FIELDS_KEY = 'fields';
	const EI_DEF_COMMANDS_KEY = 'commands';
	const EI_DEF_MODIFICATORS_KEY = 'modificators';
	const EI_DEF_FILTER_DATA_KEY = 'filterData';
	const EI_DEF_DEFAULT_SORT_KEY = 'defaultSort';

	const EI_DEF_OVERVIEW_COMMAND_ID_KEY = 'overviewCommandId';
	const EI_DEF_ENTRY_DETAIL_COMMAND_ID_KEY = 'entryDetailCommandId';
	const EI_DEF_ENTRY_EDIT_COMMAND_ID_KEY = 'entryEditCommandId';
	const EI_DEF_ENTRY_ADD_COMMAND_ID_KEY = 'entryAddCommandId';
	
	const EI_COMPONENT_CLASS_KEY = 'class';
	const EI_COMPONENT_PROPS_KEY = 'props';
	
	const EI_FIELD_OBJECT_PROPERTY_KEY = 'objectProperty';
	const EI_FIELD_ENTITY_PROPERTY_KEY = 'entityProperty';
	const EI_FIELD_LABEL_KEY = 'label';
	
	const MENU_ITEMS_KEY = 'menuItems';
	const MENU_ITEM_LABEL_KEY = 'label';
	
	const SPEC_TYPE_ENTITY = 'entity';
	const SPEC_TYPE_CUSTOM = 'custom';
	
	public static function getSpecTypes() {
		return array(self::SPEC_TYPE_ENTITY, self::SPEC_TYPE_CUSTOM);
	}
}
