import { SiEntryDeclaration } from 'src/app/si/model/structure/si-entry-declaration';
import { SiEntry } from 'src/app/si/model/content/si-entry';

export interface SiValGetResult {

	entryDeclaration: SiEntryDeclaration|null;

	entry: SiEntry|null;
}
