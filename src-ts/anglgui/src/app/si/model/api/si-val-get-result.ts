import { SiEntryDeclaration } from 'src/app/si/model/entity/si-entry-declaration';
import { SiEntry } from 'src/app/si/model/entity/si-entry';

export interface SiValGetResult {

	entryDeclaration: SiEntryDeclaration|null;

	entry: SiEntry|null;
}
