import { SiEntryDeclaration } from 'src/app/si/model/structure/si-entry-declaration';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { SiPartialContent } from 'src/app/si/model/content/si-partial-content';

export interface SiGetResult {

	entryDeclaration: SiEntryDeclaration|null;

	entry: SiEntry|null;

	partialContent: SiPartialContent|null;
}
