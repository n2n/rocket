import { SiEntry } from 'src/app/si/model/content/si-entry';
import { SiPartialContent } from 'src/app/si/model/content/si-partial-content';
import { SiDeclaration } from '../meta/si-declaration';

export interface SiGetResult {

	declaration: SiDeclaration|null;

	entry: SiEntry|null;

	partialContent: SiPartialContent|null;
}
