
import { SiFieldDeclaration } from 'src/app/si/model/entity/si-field-declaration';

export class SiFieldStructureDeclaration {

	constructor(public fieldDeclaration: SiFieldDeclaration, public type: SiStructureType,
			public children: SiFieldStructureDeclaration[] = []) {
	}
}

export enum SiStructureType {
	SIMPLE_GROUP = 'simple-group',
	MAIN_GROUP = 'main-group',
	AUTONOMIC_GROUP = 'autonomic-group',
	LIGHT_GROUP = 'light-group',
	PANEL = 'panel',
	ITEM = 'item'
}
