import { SiEntry } from '../../../content/si-entry';
import { SiDeclaration } from '../../../meta/si-declaration';

export interface BulkyEntryModel {

	getSiDeclaration(): SiDeclaration;

	getSiEntry(): SiEntry;
}
