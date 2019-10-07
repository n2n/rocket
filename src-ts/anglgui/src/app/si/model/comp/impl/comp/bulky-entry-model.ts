import { SiStructureDeclaration } from '../../../meta/si-structure-declaration';
import { SiEntryBuildup } from '../../../content/si-entry-buildup';

export interface BulkyEntryModel {
	getSiStructureDeclarations(): SiStructureDeclaration[];

	getSiEntryBuildup(): SiEntryBuildup;
}
