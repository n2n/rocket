import { SiEntryDeclaration } from 'src/app/si/model/entity/si-entry-declaration';
import { SiEntry } from 'src/app/si/model/entity/si-entry';
import { SiPartialContent } from 'src/app/si/model/entity/si-partial-content';

export interface SiGetResult {

	entryDeclaration: SiEntryDeclaration|null;

	entry: SiEntry|null;

	partialContent: SiPartialContent|null;
}
