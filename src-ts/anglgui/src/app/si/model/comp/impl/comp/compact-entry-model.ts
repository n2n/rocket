import { SiDeclaration } from '../../../meta/si-declaration';
import { SiEntry } from '../../../content/si-entry';

export interface CompactEntryModel {

	getSiDeclaration(): SiDeclaration;

	getSiEntry(): SiEntry|null;
}
